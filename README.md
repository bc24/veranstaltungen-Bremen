# Marktverzeichnis

Kuratiertes Verzeichnis lokaler Märkte (Kürbismärkte, Weihnachtsmärkte, Wochenmärkte u. a.) mit
Kartenansicht der Standorte. Jeder Eintrag ist kostenpflichtig – es gibt bewusst keine kostenlose
Basisversion. Neue Einträge durchlaufen zwingend eine Bezahlung (Stripe Checkout) und werden erst
nach redaktioneller Prüfung im Admin-Bereich veröffentlicht.

## Architektur

- **Frontend**: React (Vite) + React Router, Kartenansicht mit React-Leaflet/OpenStreetMap
- **Backend**: Node.js + Express, ORM Sequelize
- **Datenbank**: MySQL/MariaDB
- **Zahlungen**: Stripe Checkout (Testmodus-Keys erforderlich, eigene Keys in `.env` eintragen)

```
backend/    Express-API, Sequelize-Modelle, Stripe-Integration, Admin-Auth (JWT)
frontend/   React-App: Verzeichnis mit Karte/Liste/Filter, Einreichungs-Formular, Admin-Panel
```

## Datenmodell

Ein Markt (`markets`) durchläuft folgenden Lebenszyklus:

```
pending_payment  →  (Stripe-Zahlung erfolgreich, per Webhook)  →  pending_review  →  published | rejected
```

Ohne bezahlten Status (`paymentStatus = paid`) kann ein Eintrag nicht veröffentlicht werden – das
setzt technisch um, dass es keinen kostenlosen Pfad ins Verzeichnis gibt.

## Lokales Setup

### 1. Datenbank

MariaDB per Docker starten (oder eine bestehende MySQL/MariaDB-Instanz verwenden):

```bash
docker compose up -d
```

### 2. Backend

```bash
cd backend
cp .env.example .env
# .env ausfüllen: DB-Zugang, JWT_SECRET, ADMIN_EMAIL/ADMIN_PASSWORD,
# STRIPE_SECRET_KEY & STRIPE_WEBHOOK_SECRET (aus dem Stripe-Dashboard, Testmodus)
npm install
npm run seed   # legt Admin-Konto + Beispiel-Märkte an
npm run dev    # startet die API auf Port 4000
```

Stripe-Webhooks lokal testen mit der Stripe CLI:

```bash
stripe listen --forward-to localhost:4000/api/payments/webhook
```

### 3. Frontend

```bash
cd frontend
npm install
npm run dev    # startet die App auf Port 5173, proxyt /api an das Backend
```

Admin-Bereich: `http://localhost:5173/admin` (Zugangsdaten aus `ADMIN_EMAIL`/`ADMIN_PASSWORD`).

## API-Übersicht

| Methode | Pfad | Zugriff | Zweck |
|---|---|---|---|
| GET | `/api/markets` | öffentlich | Veröffentlichte Märkte, Filter über Query-Parameter (`category`, `city`, `from`, `to`, `q`) |
| GET | `/api/markets/:id` | öffentlich | Einzelner veröffentlichter Markt |
| POST | `/api/markets` | öffentlich | Neuen Markteintrag anlegen (Status `pending_payment`) |
| POST | `/api/payments/create-checkout-session` | öffentlich | Stripe-Checkout-Session für einen Eintrag erstellen |
| POST | `/api/payments/webhook` | Stripe | Zahlungsbestätigung, setzt Eintrag auf `pending_review` |
| POST | `/api/admin/login` | öffentlich | Admin-Login, liefert JWT |
| GET | `/api/admin/markets` | Admin | Alle Einträge, optional gefiltert nach `status` |
| PATCH | `/api/admin/markets/:id` | Admin | Veröffentlichen/Ablehnen |
| DELETE | `/api/admin/markets/:id` | Admin | Eintrag löschen |

## Lizenz

&copy; 2026 &ndash; Entwickelt von [Frank Panzer](https://frank-panzer.de) &ndash; [Panzer IT](https://panzerit.de)
