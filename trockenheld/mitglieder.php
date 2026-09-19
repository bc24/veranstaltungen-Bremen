<?php
require_once __DIR__ . '/includes/functions.php';

$stmt = $pdo->query(
    "SELECT * FROM benutzer WHERE profil_oeffentlich = 1
     ORDER BY aktueller_streak DESC, punkte DESC, laengster_streak DESC
     LIMIT 100"
);
$mitglieder = $stmt->fetchAll();

$seitentitel = 'Mitglieder';
require __DIR__ . '/includes/header.php';
?>

<div class="card">
    <h1 class="mt-0">Mitglieder &amp; Bestenliste</h1>
    <p class="muted">Sortiert nach aktueller Serie ohne Alkohol. Nur öffentliche Profile werden angezeigt.</p>

    <?php if (!$mitglieder): ?>
        <p class="muted">Noch keine öffentlichen Profile vorhanden.</p>
    <?php endif; ?>

    <?php foreach ($mitglieder as $i => $m): ?>
        <div class="mitglied-zeile">
            <div style="width:28px;text-align:center;font-weight:700;color:var(--text-mild);"><?= $i + 1 ?></div>
            <div class="avatar klein" style="background: <?= h($m['avatar_farbe']) ?>;"><?= h(avatar_initiale($m)) ?></div>
            <div class="mitglied-info">
                <div class="name">
                    <a href="profil.php?id=<?= (int) $m['id'] ?>"><?= h($m['anzeigename'] ?: $m['benutzername']) ?></a>
                    <?php $lvl = level_info((int) $m['punkte']); ?>
                    <span class="hilfetext">Lvl <?= (int) $lvl['level'] ?> · <?= h($lvl['titel']) ?></span>
                </div>
                <div class="meta">
                    🔥 <?= (int) $m['aktueller_streak'] ?> Tage aktuell ·
                    🏆 <?= (int) $m['laengster_streak'] ?> Tage Rekord ·
                    ⭐ <?= (int) $m['punkte'] ?> Punkte
                    <?php if ($m['wohnort']): ?> · <?= h($m['wohnort']) ?><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
