<?php
require_once __DIR__ . '/includes/functions.php';

if (eingeloggt()) {
    header('Location: dashboard.php');
    exit;
}

$fehler = [];
$eingabe = [
    'benutzername' => '',
    'email'        => '',
    'anzeigename'  => '',
    'start_datum'  => date('Y-m-d'),
    'warum_text'   => '',
    'wohnort'      => '',
    'geburtsjahr'  => '',
    'ziel_tage'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();

    $eingabe['benutzername'] = trim($_POST['benutzername'] ?? '');
    $eingabe['email']        = trim($_POST['email'] ?? '');
    $eingabe['anzeigename']  = trim($_POST['anzeigename'] ?? '');
    $eingabe['start_datum']  = trim($_POST['start_datum'] ?? date('Y-m-d'));
    $eingabe['warum_text']   = trim($_POST['warum_text'] ?? '');
    $eingabe['wohnort']      = trim($_POST['wohnort'] ?? '');
    $eingabe['geburtsjahr']  = trim($_POST['geburtsjahr'] ?? '');
    $eingabe['ziel_tage']    = trim($_POST['ziel_tage'] ?? '');
    $passwort   = $_POST['passwort'] ?? '';
    $passwort2  = $_POST['passwort_wiederholen'] ?? '';
    $datenschutzAkzeptiert = isset($_POST['datenschutz']);

    if (!preg_match('/^[A-Za-z0-9_\-\.]{3,50}$/', $eingabe['benutzername'])) {
        $fehler[] = 'Der Benutzername muss 3–50 Zeichen lang sein (Buchstaben, Zahlen, _ - .).';
    }
    if (!filter_var($eingabe['email'], FILTER_VALIDATE_EMAIL)) {
        $fehler[] = 'Bitte eine gültige E-Mail-Adresse angeben.';
    }
    if (strlen($passwort) < 8) {
        $fehler[] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
    }
    if ($passwort !== $passwort2) {
        $fehler[] = 'Die Passwörter stimmen nicht überein.';
    }
    $startDatumObj = DateTime::createFromFormat('Y-m-d', $eingabe['start_datum']);
    if (!$startDatumObj) {
        $fehler[] = 'Bitte ein gültiges Startdatum angeben.';
    }
    if (!$datenschutzAkzeptiert) {
        $fehler[] = 'Du musst der Datenschutzerklärung zustimmen, um dich zu registrieren.';
    }

    if (!$fehler) {
        $stmt = $pdo->prepare('SELECT 1 FROM benutzer WHERE benutzername = ? OR email = ?');
        $stmt->execute([$eingabe['benutzername'], $eingabe['email']]);
        if ($stmt->fetchColumn()) {
            $fehler[] = 'Benutzername oder E-Mail-Adresse wird bereits verwendet.';
        }
    }

    if (!$fehler) {
        $insert = $pdo->prepare(
            'INSERT INTO benutzer
                (benutzername, email, passwort_hash, anzeigename, avatar_farbe, warum_text,
                 wohnort, geburtsjahr, start_datum, ziel_tage, datenschutz_akzeptiert)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $zufallsFarben = ['#0f9d78', '#2f8fd6', '#c9791a', '#a24fd6', '#d9534f', '#3ba39c'];
        $insert->execute([
            $eingabe['benutzername'],
            $eingabe['email'],
            password_hash($passwort, PASSWORD_DEFAULT),
            $eingabe['anzeigename'] !== '' ? $eingabe['anzeigename'] : null,
            $zufallsFarben[array_rand($zufallsFarben)],
            $eingabe['warum_text'] !== '' ? $eingabe['warum_text'] : null,
            $eingabe['wohnort'] !== '' ? $eingabe['wohnort'] : null,
            $eingabe['geburtsjahr'] !== '' ? (int) $eingabe['geburtsjahr'] : null,
            $eingabe['start_datum'],
            $eingabe['ziel_tage'] !== '' ? (int) $eingabe['ziel_tage'] : null,
        ]);

        $_SESSION['benutzer_id'] = (int) $pdo->lastInsertId();
        flash_setzen('erfolg', 'Willkommen bei ' . APP_NAME . '! Dein Konto wurde erstellt.');
        header('Location: dashboard.php');
        exit;
    }
}

$seitentitel = 'Registrieren';
require __DIR__ . '/includes/header.php';
?>
<div class="card form-schmal">
    <h1>Konto erstellen</h1>
    <p class="muted">Starte deine Challenge ohne Alkohol – kostenlos und in 2 Minuten.</p>

    <?php if ($fehler): ?>
        <div class="flash flash-fehler">
            <ul style="margin:0;padding-left:18px;">
                <?php foreach ($fehler as $f): ?><li><?= h($f) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="feld">
            <label for="benutzername">Benutzername *</label>
            <input type="text" id="benutzername" name="benutzername" required value="<?= h($eingabe['benutzername']) ?>">
        </div>
        <div class="feld">
            <label for="email">E-Mail-Adresse *</label>
            <input type="email" id="email" name="email" required value="<?= h($eingabe['email']) ?>">
        </div>
        <div class="feld">
            <label for="anzeigename">Anzeigename (optional)</label>
            <input type="text" id="anzeigename" name="anzeigename" placeholder="z.B. Spitzname" value="<?= h($eingabe['anzeigename']) ?>">
        </div>
        <div class="feld">
            <label for="passwort">Passwort *</label>
            <input type="password" id="passwort" name="passwort" required minlength="8">
            <div class="hilfetext">Mindestens 8 Zeichen.</div>
        </div>
        <div class="feld">
            <label for="passwort_wiederholen">Passwort wiederholen *</label>
            <input type="password" id="passwort_wiederholen" name="passwort_wiederholen" required minlength="8">
        </div>
        <div class="feld">
            <label for="start_datum">Seit wann bist du dabei / möchtest du starten? *</label>
            <input type="date" id="start_datum" name="start_datum" required value="<?= h($eingabe['start_datum']) ?>">
            <div class="hilfetext">Meist einfach das heutige Datum.</div>
        </div>
        <div class="feld">
            <label for="ziel_tage">Persönliches Ziel in Tagen (optional)</label>
            <input type="number" id="ziel_tage" name="ziel_tage" min="1" value="<?= h($eingabe['ziel_tage']) ?>">
        </div>
        <div class="feld">
            <label for="warum_text">Warum machst du diese Challenge? (optional, erscheint auf deinem Profil)</label>
            <textarea id="warum_text" name="warum_text"><?= h($eingabe['warum_text']) ?></textarea>
        </div>
        <div class="feld">
            <label for="wohnort">Wohnort (optional)</label>
            <input type="text" id="wohnort" name="wohnort" value="<?= h($eingabe['wohnort']) ?>">
        </div>
        <div class="feld">
            <label for="geburtsjahr">Geburtsjahr (optional)</label>
            <input type="number" id="geburtsjahr" name="geburtsjahr" min="1900" max="<?= date('Y') ?>" value="<?= h($eingabe['geburtsjahr']) ?>">
        </div>

        <div class="feld checkbox-feld">
            <input type="checkbox" id="datenschutz" name="datenschutz" required>
            <label for="datenschutz" style="font-weight:400;">
                Ich habe die <a href="datenschutz.php" target="_blank">Datenschutzerklärung</a> gelesen und stimme der Verarbeitung meiner Daten zu. *
            </label>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">Challenge starten</button>
    </form>
    <p class="muted" style="margin-top:16px;">Schon registriert? <a href="login.php">Jetzt anmelden</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
