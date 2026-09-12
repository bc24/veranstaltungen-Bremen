# Marktverzeichnis

Kuratiertes Verzeichnis lokaler Märkte (Kürbismärkte, Weihnachtsmärkte, Wochenmärkte u. a.) mit
Kartenansicht der Standorte. Jeder Eintrag ist kostenpflichtig – es gibt bewusst keine kostenlose
Basisversion. Neue Einträge durchlaufen zwingend eine Bezahlung (Stripe Checkout) und werden erst
nach redaktioneller Prüfung im Admin-Bereich veröffentlicht.

## Architektur

- **Laufzeitumgebung**: Apache 2 + PHP 8.1+ (klassischer Server-Side-Rendering-Stack, kein Build-Schritt)
- **Datenbank**: MySQL/MariaDB, Zugriff über PDO (prepared statements)
- **Karte**: Leaflet + OpenStreetMap, eingebunden per CDN
- **Zahlungen**: Stripe Checkout (Testmodus-Keys erforderlich, eigene Keys in `.env` eintragen)
- **Admin-Auth**: PHP-Sessions + `password_hash`/`password_verify`, CSRF-Token auf allen Formularen

```
public/         Apache-DocumentRoot: alle aufrufbaren Seiten (index.php, einreichen.php, admin/, api/, payments/)
src/            PHP-Klassen: Database, Market, Auth, Payments (Composer-Autoload)
config/         .env-Loader
sql/schema.sql  Datenbankschema
bin/seed.php    Legt Admin-Konto + Beispiel-Märkte an
```

## Datenmodell

Ein Markt (`markets`) durchläuft folgenden Lebenszyklus:

```
pending_payment  →  (Stripe-Zahlung erfolgreich, per Webhook)  →  pending_review  →  published | rejected
```

`Market::updateStatus()` verweigert die Veröffentlichung serverseitig, solange `payment_status`
nicht `paid` ist – es gibt also technisch keinen kostenlosen Weg ins Verzeichnis.

## Lokales Setup

### 1. Datenbank

MariaDB per Docker starten (oder eine bestehende MySQL/MariaDB-Instanz verwenden) und Schema importieren:

```bash
docker compose up -d
mysql -h 127.0.0.1 -u root -p market_directory < sql/schema.sql
```

### 2. Konfiguration & Abhängigkeiten

```bash
cp .env.example .env
# .env ausfüllen: DB-Zugang, APP_URL, ADMIN_EMAIL/ADMIN_PASSWORD,
# STRIPE_SECRET_KEY & STRIPE_WEBHOOK_SECRET (aus dem Stripe-Dashboard, Testmodus)
composer install
php bin/seed.php   # legt Admin-Konto + Beispiel-Märkte an
```

### 3. Server starten

**Für lokale Entwicklung** (PHP-eigener Server, kein Apache nötig):

```bash
php -S localhost:8080 -t public
```

**Für den produktiven Betrieb mit Apache 2**: `public/` als `DocumentRoot` eines VHosts eintragen,
`mod_php` oder `php-fpm` (mit `mod_proxy_fcgi`) aktivieren. Eine `.htaccess` in `public/` sperrt
`.env`, `.sql` und `.md`-Dateien.

Admin-Bereich: `http://localhost:8080/admin/login.php` (Zugangsdaten aus `ADMIN_EMAIL`/`ADMIN_PASSWORD`).

Stripe-Webhooks lokal testen mit der Stripe CLI:

```bash
stripe listen --forward-to localhost:8080/payments/webhook.php
```

## Seiten-Übersicht

| Pfad | Zugriff | Zweck |
|---|---|---|
| `/index.php` | öffentlich | Verzeichnis: Karte + Liste, Filter über GET-Parameter (`category`, `city`, `from`, `to`, `q`) |
| `/api/markets.php` | öffentlich | JSON-Export der veröffentlichten Märkte (gleiche Filter) |
| `/einreichen.php` | öffentlich | Formular für neue Markteinträge, leitet zu Stripe Checkout weiter |
| `/einreichen-erfolg.php`, `/einreichen-abgebrochen.php` | öffentlich | Rückkehrseiten von Stripe |
| `/payments/webhook.php` | Stripe | Zahlungsbestätigung, setzt Eintrag auf `pending_review` |
| `/admin/login.php`, `/admin/logout.php` | öffentlich | Admin-Anmeldung |
| `/admin/dashboard.php` | Admin (Session) | Kuratierung: veröffentlichen, ablehnen, löschen |

## Lizenz

&copy; 2026 &ndash; Entwickelt von [Frank Panzer](https://frank-panzer.de) &ndash; [Panzer IT](https://panzerit.de)
