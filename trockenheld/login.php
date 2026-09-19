<?php
require_once __DIR__ . '/includes/functions.php';

if (eingeloggt()) {
    header('Location: dashboard.php');
    exit;
}

$fehler = null;
$benutzernameEingabe = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();
    $benutzernameEingabe = trim($_POST['benutzername'] ?? '');
    $passwort = $_POST['passwort'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM benutzer WHERE benutzername = ? OR email = ?');
    $stmt->execute([$benutzernameEingabe, $benutzernameEingabe]);
    $benutzer = $stmt->fetch();

    if ($benutzer && password_verify($passwort, $benutzer['passwort_hash'])) {
        session_regenerate_id(true);
        $_SESSION['benutzer_id'] = (int) $benutzer['id'];
        header('Location: dashboard.php');
        exit;
    }
    $fehler = 'Benutzername/E-Mail oder Passwort ist falsch.';
}

$seitentitel = 'Anmelden';
require __DIR__ . '/includes/header.php';
?>
<div class="card form-schmal">
    <h1>Willkommen zurück</h1>
    <?php if ($fehler): ?>
        <div class="flash flash-fehler"><?= h($fehler) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="feld">
            <label for="benutzername">Benutzername oder E-Mail</label>
            <input type="text" id="benutzername" name="benutzername" required value="<?= h($benutzernameEingabe) ?>" autofocus>
        </div>
        <div class="feld">
            <label for="passwort">Passwort</label>
            <input type="password" id="passwort" name="passwort" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Anmelden</button>
    </form>
    <p class="muted" style="margin-top:16px;">Noch kein Konto? <a href="register.php">Jetzt registrieren</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
