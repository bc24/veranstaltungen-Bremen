<?php
require_once __DIR__ . '/includes/functions.php';

if (eingeloggt()) {
    header('Location: dashboard.php');
    exit;
}

$anzahlBenutzer = (int) $pdo->query('SELECT COUNT(*) FROM benutzer')->fetchColumn();
$stmt = $pdo->query('SELECT MAX(laengster_streak) FROM benutzer');
$bestStreak = (int) $stmt->fetchColumn();

$seitentitel = null;
require __DIR__ . '/includes/header.php';
?>
<div class="hero">
    <h1>Ohne Alkohol. Jeden Tag ein Sieg. 🌱</h1>
    <p>
        Trockenheld ist dein tägliches Challenge-Game: Ein Klick bestätigt,
        dass du nichts Alkoholisches getrunken hast – dafür gibt's Punkte,
        die mit jedem Tag deiner Serie steigen ("Combo-Bonus"), Level-Aufstiege
        und satte Boni bei Woche, Monat, Halbjahr und Jahr. Ein Rückfall kostet
        dagegen deutlich mehr Punkte, als ein guter Tag bringt – Ehrlichkeit
        zu dir selbst lohnt sich.
    </p>
    <div class="hero-actions">
        <a href="register.php" class="btn btn-primary btn-lg">Kostenlos starten</a>
        <a href="login.php" class="btn btn-outline btn-lg">Anmelden</a>
    </div>

    <div class="stufen-uebersicht">
        <?php foreach (MEILENSTEINE as $daten): ?>
            <div class="stufe-badge">
                <div class="stufe-icon">🏅</div>
                <div><?= h($daten['label']) ?></div>
                <div class="stufe-punkte">+<?= (int) $daten['punkte'] ?> Punkte</div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($anzahlBenutzer > 0): ?>
    <p class="muted" style="margin-top:36px;">
        Schon <strong><?= $anzahlBenutzer ?></strong> Mitglieder auf dem Weg –
        der längste aktuelle Rekord liegt bei <strong><?= $bestStreak ?></strong> Tagen ohne Alkohol.
    </p>
    <?php endif; ?>
</div>

<div class="grid grid-3">
    <div class="card">
        <h3>✅ Täglich einchecken</h3>
        <p class="muted">Ein Klick pro Tag genügt, um deinen Fortschritt im Logbuch mit Smileys festzuhalten.</p>
    </div>
    <div class="card">
        <h3>🔥 Streaks, Level &amp; Punkte</h3>
        <p class="muted">Jeder trockene Tag bringt mehr Punkte als der davor. Bei 7, 30, 182 und 365 Tagen gibt's fette Boni und eine selbstgewählte Belohnung – ein Rückfall kostet dagegen spürbar mehr, als du an einem Tag gewinnst.</p>
    </div>
    <div class="card">
        <h3>👥 Öffentliches Profil</h3>
        <p class="muted">Zeig anderen deine Statistik oder lass dich von den Erfolgen der Community motivieren.</p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
