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
        Trockenheld ist deine tägliche Challenge: Ein Klick am Tag bestätigt,
        dass du nichts Alkoholisches getrunken hast. Sammle Streaks, verdiene
        Bonuspunkte bei Meilensteinen und gönn dir bei Woche, Monat, Halbjahr
        und Jahr eine wohlverdiente Belohnung.
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
        <h3>🔥 Streaks &amp; Punkte</h3>
        <p class="muted">Bei 7, 30, 182 und 365 Tagen am Stück gibt's Bonuspunkte und eine selbstgewählte Belohnung.</p>
    </div>
    <div class="card">
        <h3>👥 Öffentliches Profil</h3>
        <p class="muted">Zeig anderen deine Statistik oder lass dich von den Erfolgen der Community motivieren.</p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
