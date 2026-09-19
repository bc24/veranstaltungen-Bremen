<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM benutzer WHERE id = ?');
$stmt->execute([$id]);
$profil = $stmt->fetch();

if (!$profil) {
    http_response_code(404);
    $seitentitel = 'Profil nicht gefunden';
    require __DIR__ . '/includes/header.php';
    echo '<div class="card"><h1>Profil nicht gefunden</h1><p><a href="mitglieder.php">Zurück zur Mitgliederliste</a></p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$eigenesProfil = eingeloggt() && (int) $_SESSION['benutzer_id'] === $profil['id'];

if (!$profil['profil_oeffentlich'] && !$eigenesProfil) {
    $seitentitel = 'Privates Profil';
    require __DIR__ . '/includes/header.php';
    echo '<div class="card"><h1>Dieses Profil ist privat</h1><p class="muted">' . h($profil['anzeigename'] ?: $profil['benutzername']) . ' hat sein Profil nicht öffentlich freigegeben.</p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

if ($eigenesProfil) {
    $profil = abgelaufenen_streak_aktualisieren($pdo, $profil);
}

$stmt = $pdo->prepare('SELECT * FROM meilensteine WHERE benutzer_id = ? ORDER BY erreicht_am DESC');
$stmt->execute([$profil['id']]);
$meilensteine = $stmt->fetchAll();

$stmt = $pdo->prepare(
    'SELECT b.* FROM belohnungen b WHERE b.benutzer_id = ? ORDER BY b.erstellt_am DESC LIMIT 10'
);
$stmt->execute([$profil['id']]);
$belohnungen = $stmt->fetchAll();

$name = $profil['anzeigename'] ?: $profil['benutzername'];
$level = level_info((int) $profil['punkte']);
$seitentitel = 'Profil von ' . $name;
require __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="profil-kopf">
        <div class="avatar" style="background: <?= h($profil['avatar_farbe']) ?>;"><?= h(avatar_initiale($profil)) ?></div>
        <div>
            <h1 class="mt-0" style="margin-bottom:4px;"><?= h($name) ?></h1>
            <p class="muted" style="margin:0;">
                @<?= h($profil['benutzername']) ?> ·
                <span class="level-badge" style="padding:2px 10px;font-size:0.8rem;">Lvl <?= (int) $level['level'] ?></span>
                <?= h($level['titel']) ?>
                <?php if ($profil['wohnort']): ?> · <?= h($profil['wohnort']) ?><?php endif; ?>
                <?php if (!$profil['profil_oeffentlich']): ?> · <span title="Nur für dich sichtbar">🔒 privat</span><?php endif; ?>
            </p>
        </div>
        <?php if ($eigenesProfil): ?>
            <a href="profil_bearbeiten.php" class="btn btn-outline" style="margin-left:auto;">Profil bearbeiten</a>
        <?php endif; ?>
    </div>

    <?php if ($profil['bio']): ?>
        <p style="margin-top:22px;"><?= nl2br(h($profil['bio'])) ?></p>
    <?php endif; ?>
    <?php if ($profil['warum_text']): ?>
        <p style="margin-top:10px;"><em>„<?= nl2br(h($profil['warum_text'])) ?>"</em></p>
    <?php endif; ?>

    <div class="profil-stats">
        <div>
            <div class="stat-wert"><?= (int) $profil['aktueller_streak'] ?></div>
            <div class="stat-label">Aktuelle Serie (Tage)</div>
        </div>
        <div>
            <div class="stat-wert"><?= (int) $profil['laengster_streak'] ?></div>
            <div class="stat-label">Längste Serie (Tage)</div>
        </div>
        <div>
            <div class="stat-wert"><?= tage_seit($profil['start_datum']) ?></div>
            <div class="stat-label">Dabei seit</div>
        </div>
        <div>
            <div class="stat-wert"><?= (int) $profil['punkte'] ?></div>
            <div class="stat-label">Bonuspunkte</div>
        </div>
        <?php if ($profil['ziel_tage']): ?>
        <div>
            <div class="stat-wert"><?= (int) $profil['ziel_tage'] ?></div>
            <div class="stat-label">Persönliches Ziel (Tage)</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h3>Erreichte Meilensteine</h3>
    <?php if (!$meilensteine): ?>
        <p class="muted">Noch keine Meilensteine erreicht.</p>
    <?php else: ?>
        <div class="meilenstein-liste">
            <?php foreach ($meilensteine as $m): ?>
                <div class="meilenstein erreicht">
                    <div class="icon">🏅</div>
                    <div><?= h(MEILENSTEINE[$m['typ']]['label']) ?></div>
                    <div class="hilfetext"><?= h($m['erreicht_am']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($belohnungen): ?>
<div class="card">
    <h3>Belohnungen</h3>
    <ul>
        <?php foreach ($belohnungen as $b): ?>
            <li><?= h($b['text']) ?> <span class="hilfetext">(<?= h(substr($b['erstellt_am'], 0, 10)) ?>)</span></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
