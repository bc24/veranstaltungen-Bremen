<?php
/**
 * Konfigurationsdatei für Trockenheld
 *
 * Anleitung:
 * 1. Diese Datei kopieren und in "config.php" umbenennen.
 * 2. Die Zugangsdaten unten mit deinen echten MySQL-Daten füllen
 *    (die bekommst du von deinem Webhoster / aus deinem Hosting-Panel).
 * 3. config.php NIEMALS öffentlich zugänglich machen (die mitgelieferte
 *    .htaccess blockiert den direkten Zugriff bereits).
 */

// --- Datenbank ---------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'trockenheld');
define('DB_USER', 'DEIN_DB_BENUTZER');
define('DB_PASS', 'DEIN_DB_PASSWORT');

// --- Anwendung -----------------------------------------------------------
define('APP_NAME', 'Trockenheld');
// Vollständige URL zur App inkl. abschließendem Slash, z.B. https://deine-domain.de/trockenheld/
define('APP_URL', 'https://deine-domain.de/trockenheld/');

// Absender-Adresse für Erinnerungs-E-Mails (muss auf deinem Server als
// Absender erlaubt sein, sonst landen die Mails im Spam / werden abgelehnt)
define('MAIL_ABSENDER', 'frank@panzerit.de');
define('MAIL_ABSENDER_NAME', APP_NAME);

// Geheimer Schlüssel für Session-/CSRF-Schutz – hier eine eigene,
// zufällige Zeichenkette eintragen (z.B. per Passwort-Generator erzeugen)
define('APP_SECRET', 'BITTE_AENDERN_ZUFAELLIGE_ZEICHENKETTE');

// --- Zeitzone --------------------------------------------------------
date_default_timezone_set('Europe/Berlin');
