<?php
/**
 * Gemeinsamer Seitenkopf. Erwartet optional die Variable $seitentitel.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';

$eingeloggterBenutzer = eingeloggt() ? aktueller_benutzer($pdo) : null;
$titel = isset($seitentitel) ? $seitentitel . ' – ' . APP_NAME : APP_NAME . ' – Deine Challenge ohne Alkohol';
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($titel) ?></title>
<meta name="description" content="Trockenheld – die Web-App für deine Challenge ohne Alkohol. Täglich einchecken, Streaks sammeln, Meilensteine feiern.">
<link rel="stylesheet" href="<?= h(APP_URL) ?>assets/css/style.css">
<script src="<?= h(APP_URL) ?>assets/js/app.js" defer></script>
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?= h(APP_URL) ?>index.php">
            <span class="logo-icon">🌱</span> <?= h(APP_NAME) ?>
        </a>
        <nav class="main-nav">
            <?php if ($eingeloggterBenutzer): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="mitglieder.php">Mitglieder</a>
                <a href="profil.php?id=<?= (int) $eingeloggterBenutzer['id'] ?>">Mein Profil</a>
                <a href="profil_bearbeiten.php">Einstellungen</a>
                <a href="logout.php">Abmelden</a>
            <?php else: ?>
                <a href="login.php">Anmelden</a>
                <a href="register.php" class="nav-cta">Jetzt starten</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container main-content">
<?php foreach (flash_ausgeben() as $flash): ?>
    <div class="flash flash-<?= h($flash['typ']) ?>"><?= h($flash['text']) ?></div>
<?php endforeach; ?>
