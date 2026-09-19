<?php
/**
 * Hilfsfunktionen: Streak-Berechnung, Meilensteine, Bonuspunkte, Sicherheit.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Schwellenwerte der Challenge-Stufen (Tage im Streak) und die dafür
// vergebenen Bonuspunkte.
const MEILENSTEINE = [
    'woche'    => ['tage' => 7,   'punkte' => 50,   'label' => 'Eine Woche'],
    'monat'    => ['tage' => 30,  'punkte' => 250,  'label' => 'Ein Monat'],
    'halbjahr' => ['tage' => 182, 'punkte' => 1000, 'label' => 'Ein halbes Jahr'],
    'jahr'     => ['tage' => 365, 'punkte' => 3000, 'label' => 'Ein ganzes Jahr'],
];

// Tages-Bonus: Je länger die aktuelle Serie, desto mehr Punkte gibt es pro
// Tag ("Combo"-Mechanik). Absteigend sortiert nach Mindest-Streak-Tag.
const PUNKTE_STUFEN = [
    ['ab_tag' => 365, 'punkte' => 50, 'icon' => '👑', 'label' => 'Legenden-Modus'],
    ['ab_tag' => 182, 'punkte' => 30, 'icon' => '💎', 'label' => 'Diamant-Serie'],
    ['ab_tag' => 30,  'punkte' => 20, 'icon' => '🔥', 'label' => 'Feuer-Serie'],
    ['ab_tag' => 7,   'punkte' => 15, 'icon' => '⚡', 'label' => 'Powerstreak'],
    ['ab_tag' => 1,   'punkte' => 10, 'icon' => '🌱', 'label' => 'Start-Bonus'],
];

// Punktabzug, wenn ein Rückfall gemeldet wird – bewusst deutlich höher als
// der tägliche Gewinn, damit sich das Durchhalten spürbar mehr lohnt.
const RUECKFALL_PUNKTE_ABZUG = 150;

// Level-System: Titel richten sich nach den insgesamt gesammelten Punkten.
const LEVEL_STUFEN = [
    ['ab_punkte' => 0,     'titel' => 'Trocken-Neuling'],
    ['ab_punkte' => 150,   'titel' => 'Durchhalter'],
    ['ab_punkte' => 500,   'titel' => 'Kämpfer'],
    ['ab_punkte' => 1200,  'titel' => 'Krieger'],
    ['ab_punkte' => 3000,  'titel' => 'Champion'],
    ['ab_punkte' => 6000,  'titel' => 'Meister'],
    ['ab_punkte' => 12000, 'titel' => 'Trockenheld-Legende'],
];

function punkte_fuer_streak_tag(int $streakTag): int
{
    foreach (PUNKTE_STUFEN as $stufe) {
        if ($streakTag >= $stufe['ab_tag']) {
            return $stufe['punkte'];
        }
    }
    return 10;
}

function stufe_fuer_streak_tag(int $streakTag): array
{
    foreach (PUNKTE_STUFEN as $stufe) {
        if ($streakTag >= $stufe['ab_tag']) {
            return $stufe;
        }
    }
    return end(PUNKTE_STUFEN);
}

/**
 * Liefert Level-Nummer, Titel, Fortschritt und Punkte bis zum nächsten
 * Level für die Anzeige einer Fortschrittsleiste im Dashboard.
 */
function level_info(int $punkte): array
{
    $punkte = max(0, $punkte);
    $anzahl = count(LEVEL_STUFEN);

    $aktuellerIndex = 0;
    foreach (LEVEL_STUFEN as $i => $stufe) {
        if ($punkte >= $stufe['ab_punkte']) {
            $aktuellerIndex = $i;
        }
    }

    $aktuelleStufe = LEVEL_STUFEN[$aktuellerIndex];
    $naechsteStufe = LEVEL_STUFEN[$aktuellerIndex + 1] ?? null;

    if ($naechsteStufe === null) {
        $fortschrittProzent = 100;
        $fehlendePunkte = 0;
    } else {
        $spanne = $naechsteStufe['ab_punkte'] - $aktuelleStufe['ab_punkte'];
        $erreicht = $punkte - $aktuelleStufe['ab_punkte'];
        $fortschrittProzent = $spanne > 0 ? (int) round(min(100, max(0, $erreicht / $spanne * 100))) : 100;
        $fehlendePunkte = $naechsteStufe['ab_punkte'] - $punkte;
    }

    return [
        'level'               => $aktuellerIndex + 1,
        'titel'               => $aktuelleStufe['titel'],
        'naechster_titel'     => $naechsteStufe['titel'] ?? null,
        'fortschritt_prozent' => $fortschrittProzent,
        'fehlende_punkte'     => $fehlendePunkte,
        'ist_max_level'       => $naechsteStufe === null,
    ];
}

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

function hat_heute_rueckfall(PDO $pdo, int $benutzerId): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM rueckfaelle WHERE benutzer_id = ? AND rueckfall_datum = CURDATE()');
    $stmt->execute([$benutzerId]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Trägt den heutigen Check-in ein, aktualisiert den Streak und vergibt
 * den Tages-Bonus sowie ggf. neue Meilensteine samt Bonuspunkten.
 *
 * @return array{streak:int, tagesbonus:int, neue_meilensteine: array, bereits_erledigt: bool}
 */
function checkin_durchfuehren(PDO $pdo, array $benutzer, ?string $notiz): array
{
    $heute = new DateTimeImmutable('today');

    if (hat_heute_eingecheckt($pdo, $benutzer['id']) || hat_heute_rueckfall($pdo, $benutzer['id'])) {
        return [
            'streak' => (int) $benutzer['aktueller_streak'],
            'tagesbonus' => 0,
            'neue_meilensteine' => [],
            'bereits_erledigt' => true,
        ];
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

    $tagesbonus = punkte_fuer_streak_tag($neuerStreak);
    $neueMeilensteine = [];
    $neuePunkte = $tagesbonus;

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

    return [
        'streak' => $neuerStreak,
        'tagesbonus' => $tagesbonus,
        'neue_meilensteine' => $neueMeilensteine,
        'bereits_erledigt' => false,
    ];
}

/**
 * Meldet einen Rückfall ("ich habe heute getrunken"): zieht Punkte ab
 * (nie unter 0) und setzt den aktuellen Streak zurück auf 0.
 *
 * @return array{abzug:int, bereits_erledigt: bool}
 */
function rueckfall_melden(PDO $pdo, array $benutzer, ?string $notiz): array
{
    if (hat_heute_eingecheckt($pdo, $benutzer['id']) || hat_heute_rueckfall($pdo, $benutzer['id'])) {
        return ['abzug' => 0, 'bereits_erledigt' => true];
    }

    $abzug = min(RUECKFALL_PUNKTE_ABZUG, (int) $benutzer['punkte']);

    $insert = $pdo->prepare(
        'INSERT INTO rueckfaelle (benutzer_id, rueckfall_datum, punkte_abgezogen, notiz) VALUES (?, CURDATE(), ?, ?)'
    );
    $insert->execute([$benutzer['id'], RUECKFALL_PUNKTE_ABZUG, $notiz !== '' ? $notiz : null]);

    $update = $pdo->prepare(
        'UPDATE benutzer
         SET punkte = GREATEST(punkte - ?, 0), aktueller_streak = 0, letzter_checkin = CURDATE()
         WHERE id = ?'
    );
    $update->execute([RUECKFALL_PUNKTE_ABZUG, $benutzer['id']]);

    return ['abzug' => $abzug, 'bereits_erledigt' => false];
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

/**
 * Nächste höhere Tages-Bonus-Stufe (z.B. "noch 3 Tage bis Feuer-Serie +20/Tag").
 */
function naechste_punkte_stufe(int $aktuellerStreak): ?array
{
    $aufsteigend = array_reverse(PUNKTE_STUFEN);
    foreach ($aufsteigend as $stufe) {
        if ($stufe['ab_tag'] > 1 && $stufe['ab_tag'] > $aktuellerStreak) {
            return ['fehlende_tage' => $stufe['ab_tag'] - $aktuellerStreak] + $stufe;
        }
    }
    return null;
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
