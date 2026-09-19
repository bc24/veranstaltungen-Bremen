# Trockenheld

Web-App für die tägliche Challenge "kein Alkohol": ein Klick pro Tag,
Logbuch mit Smileys, Streaks, Bonuspunkte bei 7 / 30 / 182 / 365 Tagen
und öffentliche Profile mit Statistik.

## Voraussetzungen

- Webserver mit PHP 8.0 oder neuer (PDO- und mysqli-Erweiterung aktiv)
- MySQL / MariaDB Datenbank
- Für den Versand der Erinnerungs-Mails: `mail()` muss auf dem Server
  funktionieren (bei den meisten Hostern automatisch der Fall), oder ein
  eigener Cronjob-Mechanismus

## Installation

1. **Dateien hochladen**
   Den kompletten Ordner `trockenheld/` per FTP/SFTP in dein Webverzeichnis
   laden (z. B. nach `/www/trockenheld/` oder direkt ins Hauptverzeichnis,
   falls die App unter der Hauptdomain laufen soll).

2. **Datenbank anlegen**
   In deinem Hosting-Panel (z. B. Plesk, cPanel, phpMyAdmin) eine neue
   MySQL-Datenbank samt Benutzer anlegen und dir Benutzername, Passwort
   sowie Datenbankname/Host notieren.

3. **Datenbankschema importieren**
   Die Datei `sql/schema.sql` in die neue Datenbank importieren, z. B. in
   phpMyAdmin über "Importieren", oder per Konsole:
   ```
   mysql -u DEIN_DB_USER -p DEIN_DB_NAME < sql/schema.sql
   ```

4. **Konfiguration anlegen**
   `config.example.php` kopieren und in `config.php` umbenennen. Darin
   folgende Werte eintragen:
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` – deine Datenbankzugangsdaten
   - `APP_URL` – die vollständige URL zur App, z. B.
     `https://deine-domain.de/trockenheld/`
   - `MAIL_ABSENDER` – Absenderadresse für Erinnerungsmails
   - `APP_SECRET` – eine eigene, zufällige Zeichenkette

   `config.php` wird durch die mitgelieferte `.htaccess` vor direktem
   Zugriff über den Browser geschützt.

5. **Erinnerungs-Cronjob einrichten**
   Damit die App dich täglich an den Check-in erinnert, im Hosting-Panel
   (Cronjobs) einen täglichen Job einrichten, z. B. um 20:00 Uhr:
   ```
   php /pfad/zu/trockenheld/cron/daily_reminder.php
   ```
   Steht kein CLI-Cron zur Verfügung, kann der Cronjob alternativ die URL
   `https://deine-domain.de/trockenheld/cron/daily_reminder.php?schluessel=...`
   aufrufen – dazu vorher in `cron/daily_reminder.php` einen eigenen
   `CRON_SCHLUESSEL` eintragen (Platzhalter im Code suchen).

6. **HTTPS aktivieren**
   Aus Datenschutzgründen (Passwörter, Login) sollte die Seite nur über
   HTTPS erreichbar sein (z. B. kostenloses Let's-Encrypt-Zertifikat über
   dein Hosting-Panel aktivieren).

7. **Fertig!**
   Seite aufrufen, registrieren, loslegen.

## Struktur

```
trockenheld/
├── assets/            CSS/JS
├── cron/              Erinnerungs-Mail-Skript
├── includes/          PHP-Hilfsfunktionen, DB-Verbindung, Header/Footer
├── sql/schema.sql      Datenbankschema zum Importieren
├── config.example.php Vorlage für config.php
├── index.php           Startseite
├── register.php / login.php / logout.php
├── dashboard.php       Check-in, Logbuch, Meilensteine
├── profil.php          Öffentliches Profil (?id=...)
├── profil_bearbeiten.php  Eigene Einstellungen
├── mitglieder.php      Mitgliederliste / Bestenliste
├── impressum.php / datenschutz.php
```

## Meilensteine & Bonuspunkte

| Stufe       | Tage am Stück | Bonuspunkte |
|-------------|---------------|-------------|
| Eine Woche  | 7             | 50          |
| Ein Monat   | 30            | 250         |
| Ein halbes Jahr | 182       | 1000        |
| Ein ganzes Jahr | 365       | 3000        |

Die Werte lassen sich in `includes/functions.php` in der Konstante
`MEILENSTEINE` anpassen.
