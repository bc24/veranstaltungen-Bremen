<?php
/**
 * Hilfsfunktionen: Streak-Berechnung, Meilensteine, Bonuspunkte, Sicherheit.
 */

require_once __DIR__ . '/db.php';

// Schwellenwerte der Challenge-Stufen (Tage im Streak) und die dafür
// vergebenen Bonuspunkte.
const MEILENSTEINE = [
    'woche'    => ['tage' => 7,   'punkte' => 50,   'label' => 'Eine Woche'],
    'monat'    => ['tage' => 30,  'punkte' => 250,  'label' => 'Ein Monat'],
    'halbjahr' => ['tage' => 182, 'punkte' => 1000, 'label' => 'Ein halbes Jahr'],
    'jahr'     => ['tage' => 365, 'punkte' => 3000, 'label' => 'Ein ganzes Jahr'],
];

function h(?string $text): string
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_pruefen(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Sicherheitsprüfung fehlgeschlagen (ungültiges Formular-Token). Bitte Seite neu laden und erneut versuchen.');
    }
}

function eingeloggt(): bool
{
    return !empty($_SESSION['benutzer_id']);
}

function login_erzwingen(): void
{
    if (!eingeloggt()) {
        header('Location: login.php');
        exit;
    }
}

function aktueller_benutzer(PDO $pdo): ?array
{
    if (!eingeloggt()) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM benutzer WHERE id = ?');
    $stmt->execute([$_SESSION['benutzer_id']]);
    $benutzer = $stmt->fetch();
    return $benutzer ?: null;
}

function flash_setzen(string $typ, string $nachricht): void
{
    $_SESSION['flash'][] = ['typ' => $typ, 'text' => $nachricht];
}

function flash_ausgeben(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/**
 * Initialen-Avatar-Daten (Buchstabe + Farbe) für einen Benutzer.
 */
function avatar_initiale(array $benutzer): string
{
    $name = $benutzer['anzeigename'] ?: $benutzer['benutzername'];
    return mb_strtoupper(mb_substr($name, 0, 1));
}

/**
 * Setzt den gespeicherten Streak zurück auf 0, falls seit dem letzten
 * Check-in mehr als ein Tag vergangen ist (Streak ist "kalt" geworden,
 * ohne dass der Benutzer sich seitdem eingeloggt / eingecheckt hat).
 * Sollte bei jedem Dashboard-Aufruf aufgerufen werden.
 */
function abgelaufenen_streak_aktualisieren(PDO $pdo, array $benutzer): array
{
    if ($benutzer['letzter_checkin'] === null) {
        return $benutzer;
    }

    $heute    = new DateTimeImmutable('today');
    $letzter  = new DateTimeImmutable($benutzer['letzter_checkin']);
    $diffTage = (int) $heute->diff($letzter)->format('%a');

    // Mehr als 1 Tag Abstand = Streak gerissen (heute noch kein Check-in,
    // gestern auch keiner)
    if ($letzter < $heute->modify('-1 day') && $benutzer['aktueller_streak'] > 0) {
        $stmt = $pdo->prepare('UPDATE benutzer SET aktueller_streak = 0 WHERE id = ?');
        $stmt->execute([$benutzer['id']]);
        $benutzer['aktueller_streak'] = 0;
    }

    return $benutzer;
}

function hat_heute_eingecheckt(PDO $pdo, int $benutzerId): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM checkins WHERE benutzer_id = ? AND checkin_datum = CURDATE()');
    $stmt->execute([$benutzerId]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Trägt den heutigen Check-in ein, aktualisiert den Streak und vergibt
 * ggf. neue Meilensteine samt Bonuspunkten.
 *
 * @return array{streak:int, neue_meilensteine: array}
 */
function checkin_durchfuehren(PDO $pdo, array $benutzer, ?string $notiz): array
{
    $heute = new DateTimeImmutable('today');

    if (hat_heute_eingecheckt($pdo, $benutzer['id'])) {
        return ['streak' => (int) $benutzer['aktueller_streak'], 'neue_meilensteine' => []];
    }

    $stmt = $pdo->prepare('INSERT INTO checkins (benutzer_id, checkin_datum, notiz) VALUES (?, CURDATE(), ?)');
    $stmt->execute([$benutzer['id'], $notiz !== '' ? $notiz : null]);

    // Neuen Streak berechnen
    $neuerStreak = 1;
    if ($benutzer['letzter_checkin'] !== null) {
        $letzter = new DateTimeImmutable($benutzer['letzter_checkin']);
        if ($letzter->format('Y-m-d') === $heute->modify('-1 day')->format('Y-m-d')) {
            $neuerStreak = (int) $benutzer['aktueller_streak'] + 1;
        }
    }

    $neuerLaengsterStreak = max((int) $benutzer['laengster_streak'], $neuerStreak);
    $streakStart = $heute->modify('-' . ($neuerStreak - 1) . ' day')->format('Y-m-d');

    $neueMeilensteine = [];
    $neuePunkte = 0;

    foreach (MEILENSTEINE as $typ => $daten) {
        if ($neuerStreak === $daten['tage']) {
            $insert = $pdo->prepare(
                'INSERT IGNORE INTO meilensteine (benutzer_id, typ, streak_tage, streak_start, punkte_erhalten, erreicht_am)
                 VALUES (?, ?, ?, ?, ?, CURDATE())'
            );
            $insert->execute([$benutzer['id'], $typ, $daten['tage'], $streakStart, $daten['punkte']]);

            if ($insert->rowCount() > 0) {
                $neueMeilensteine[] = ['typ' => $typ] + $daten;
                $neuePunkte += $daten['punkte'];
            }
        }
    }

    $update = $pdo->prepare(
        'UPDATE benutzer
         SET aktueller_streak = ?, laengster_streak = ?, letzter_checkin = CURDATE(), punkte = punkte + ?
         WHERE id = ?'
    );
    $update->execute([$neuerStreak, $neuerLaengsterStreak, $neuePunkte, $benutzer['id']]);

    return ['streak' => $neuerStreak, 'neue_meilensteine' => $neueMeilensteine];
}

/**
 * Liefert die Check-in-Tage der letzten $tage Tage als Datum => bool.
 */
function checkin_kalender(PDO $pdo, int $benutzerId, int $tage = 35): array
{
    $stmt = $pdo->prepare(
        'SELECT checkin_datum FROM checkins
         WHERE benutzer_id = ? AND checkin_datum >= DATE_SUB(CURDATE(), INTERVAL ? DAY)'
    );
    $stmt->execute([$benutzerId, $tage]);
    $eingecheckt = array_flip(array_column($stmt->fetchAll(), 'checkin_datum'));

    $kalender = [];
    for ($i = $tage - 1; $i >= 0; $i--) {
        $datum = (new DateTimeImmutable("-$i day"))->format('Y-m-d');
        $kalender[$datum] = isset($eingecheckt[$datum]);
    }
    return $kalender;
}

function naechster_meilenstein(int $aktuellerStreak): ?array
{
    foreach (MEILENSTEINE as $typ => $daten) {
        if ($aktuellerStreak < $daten['tage']) {
            return ['typ' => $typ, 'fehlende_tage' => $daten['tage'] - $aktuellerStreak] + $daten;
        }
    }
    return null;
}

function tage_seit(string $datum): int
{
    $start = new DateTimeImmutable($datum);
    $heute = new DateTimeImmutable('today');
    return (int) $start->diff($heute)->format('%a');
}
