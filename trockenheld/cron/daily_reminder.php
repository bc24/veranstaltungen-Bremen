<?php
/**
 * Erinnerungs-E-Mail an alle Mitglieder, die sich heute noch nicht
 * eingecheckt und die Erinnerung nicht deaktiviert haben.
 *
 * Einrichtung als Cronjob (z.B. im Hosting-Panel / per crontab), z.B.
 * täglich um 20:00 Uhr:
 *
 *   0 20 * * *  php /pfad/zu/trockenheld/cron/daily_reminder.php
 *
 * Alternativ, falls kein CLI-Cron verfügbar ist, kann die Datei auch
 * über einen HTTP-Cronjob aufgerufen werden – dann unbedingt den
 * CRON_SCHLUESSEL unten setzen und in der Cron-URL mitschicken:
 *   https://deine-domain.de/trockenheld/cron/daily_reminder.php?schluessel=DEIN_SCHLUESSEL
 */

require_once __DIR__ . '/../includes/functions.php';

// Schutz gegen Aufruf per Browser/HTTP durch Fremde. Bei CLI-Aufruf
// (normaler Cronjob) ist php_sapi_name() === 'cli' und die Prüfung entfällt.
if (php_sapi_name() !== 'cli') {
    define('CRON_SCHLUESSEL', 'BITTE_EIGENEN_GEHEIMEN_SCHLUESSEL_EINTRAGEN');
    if (($_GET['schluessel'] ?? '') !== CRON_SCHLUESSEL) {
        http_response_code(403);
        die('Zugriff verweigert.');
    }
}

$stmt = $pdo->query(
    "SELECT id, email, benutzername, anzeigename, aktueller_streak
     FROM benutzer
     WHERE email_erinnerung = 1
       AND (letzter_checkin IS NULL OR letzter_checkin < CURDATE())"
);
$empfaenger = $stmt->fetchAll();

$gesendet = 0;
foreach ($empfaenger as $benutzer) {
    $name = $benutzer['anzeigename'] ?: $benutzer['benutzername'];
    $betreff = '⏰ ' . APP_NAME . ': Heute schon eingecheckt?';
    $link = APP_URL . 'dashboard.php';

    $text = "Hallo $name,\n\n"
        . "heute hast du dich bei " . APP_NAME . " noch nicht eingecheckt.\n"
        . "Dein aktueller Streak: {$benutzer['aktueller_streak']} Tag(e) ohne Alkohol.\n\n"
        . "Jetzt einchecken: $link\n\n"
        . "Diese Erinnerung kannst du jederzeit in deinen Einstellungen abschalten:\n"
        . APP_URL . "profil_bearbeiten.php\n";

    $header = 'From: ' . MAIL_ABSENDER_NAME . ' <' . MAIL_ABSENDER . ">\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n";

    $erfolg = mail($benutzer['email'], '=?UTF-8?B?' . base64_encode($betreff) . '?=', $text, $header);
    if ($erfolg) {
        $gesendet++;
    }
}

if (php_sapi_name() === 'cli') {
    echo "Erinnerungen versendet: $gesendet / " . count($empfaenger) . "\n";
} else {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Erinnerungen versendet: $gesendet / " . count($empfaenger);
}
