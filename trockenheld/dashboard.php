<?php
require_once __DIR__ . '/includes/functions.php';
login_erzwingen();

$benutzer = aktueller_benutzer($pdo);
$benutzer = abgelaufenen_streak_aktualisieren($pdo, $benutzer);

$geradeGewonnenePunkte = 0;
$geradeMeilensteine = [];
$geradeLevelUp = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();
    $aktion = $_POST['aktion'] ?? '';

    if ($aktion === 'checkin') {
        $notiz = trim($_POST['notiz'] ?? '');
        $levelVorher = level_info((int) $benutzer['punkte'])['level'];
        $ergebnis = checkin_durchfuehren($pdo, $benutzer, $notiz);
        $benutzer = aktueller_benutzer($pdo);

        if (!$ergebnis['bereits_erledigt']) {
            $levelNachher = level_info((int) $benutzer['punkte'])['level'];
            if ($ergebnis['neue_meilensteine']) {
                $labels = array_map(fn($m) => $m['label'], $ergebnis['neue_meilensteine']);
                flash_setzen('erfolg', '🎉 Meilenstein erreicht: ' . implode(', ', $labels) . '! +' . (int) $ergebnis['tagesbonus'] . ' Punkte Tagesbonus. Trag dir unten eine Belohnung ein!');
            } else {
                flash_setzen('erfolg', 'Stark! +' . (int) $ergebnis['tagesbonus'] . ' Punkte. Streak: ' . $ergebnis['streak'] . ' Tag(e) am Stück.');
            }
            if ($levelNachher > $levelVorher) {
                flash_setzen('info', '⭐ Level-Up! Du bist jetzt „' . level_info((int) $benutzer['punkte'])['titel'] . '" (Level ' . $levelNachher . ').');
            }
        }
        header('Location: dashboard.php');
        exit;
    }

    if ($aktion === 'rueckfall') {
        $notiz = trim($_POST['notiz'] ?? '');
        $ergebnis = rueckfall_melden($pdo, $benutzer, $notiz);
        if (!$ergebnis['bereits_erledigt']) {
            flash_setzen('fehler', 'Kein Problem, das gehört dazu. −' . (int) $ergebnis['abzug'] . ' Punkte, Serie zurückgesetzt. Morgen ist ein neuer Tag – du schaffst das!');
        }
        header('Location: dashboard.php');
        exit;
    }

    if ($aktion === 'belohnung_speichern') {
        $text = trim($_POST['belohnung_text'] ?? '');
        $meilensteinId = (int) ($_POST['meilenstein_id'] ?? 0);
        if ($text !== '') {
            $insert = $pdo->prepare('INSERT INTO belohnungen (benutzer_id, meilenstein_id, text) VALUES (?, ?, ?)');
            $insert->execute([$benutzer['id'], $meilensteinId ?: null, $text]);
            flash_setzen('erfolg', 'Belohnung gespeichert – gönn sie dir!');
        }
        header('Location: dashboard.php');
        exit;
    }
}

$heuteErledigt = hat_heute_eingecheckt($pdo, $benutzer['id']);
$heuteRueckfall = hat_heute_rueckfall($pdo, $benutzer['id']);
$kalender = checkin_kalender($pdo, $benutzer['id'], 42);
$naechsterMeilenstein = naechster_meilenstein((int) $benutzer['aktueller_streak']);
$naechsteStufe = naechste_punkte_stufe((int) $benutzer['aktueller_streak']);
$aktuelleStufe = stufe_fuer_streak_tag(max(1, (int) $benutzer['aktueller_streak']));
$level = level_info((int) $benutzer['punkte']);

$stmt = $pdo->prepare('SELECT * FROM meilensteine WHERE benutzer_id = ? ORDER BY erreicht_am DESC');
$stmt->execute([$benutzer['id']]);
$erreichteMeilensteine = $stmt->fetchAll();

// Meilensteine ohne eingetragene Belohnung -> Belohnungsformular anzeigen
$stmt = $pdo->prepare(
    'SELECT m.* FROM meilensteine m
     LEFT JOIN belohnungen b ON b.meilenstein_id = m.id
     WHERE m.benutzer_id = ? AND b.id IS NULL
     ORDER BY m.erreicht_am DESC'
);
$stmt->execute([$benutzer['id']]);
$offeneBelohnungen = $stmt->fetchAll();

$seitentitel = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="grid grid-2">
    <div class="card checkin-box">
        <h2>Hast du heute nichts Alkoholisches getrunken?</h2>
        <p class="streak-zahl" data-count-up><?= (int) $benutzer['aktueller_streak'] ?></p>
        <p class="streak-label"><?= h($aktuelleStufe['icon']) ?> Tage am Stück ohne Alkohol · <?= h($aktuelleStufe['label']) ?></p>

        <?php if (!$heuteErledigt && !$heuteRueckfall): ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="aktion" value="checkin">
                <div class="feld" style="max-width:320px;margin:16px auto;">
                    <label for="notiz">Notiz für heute (optional)</label>
                    <input type="text" id="notiz" name="notiz" maxlength="255" placeholder="z.B. Wie fühlst du dich?">
                </div>
                <button type="submit" class="checkin-button">
                    Ja, nichts<br>getrunken! ✔<br><span style="font-size:0.8rem;font-weight:600;">+<?= (int) $aktuelleStufe['punkte'] ?> Punkte</span>
                </button>
            </form>

            <form method="post" class="rueckfall-form" data-confirm="Bist du sicher? Das kostet dich <?= RUECKFALL_PUNKTE_ABZUG ?> Punkte und deine Serie startet wieder bei 0. Ehrlichkeit zählt – du schaffst das trotzdem!">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="aktion" value="rueckfall">
                <button type="submit" class="btn-rueckfall">Ich habe heute getrunken 💔 (−<?= RUECKFALL_PUNKTE_ABZUG ?> Punkte)</button>
            </form>
        <?php elseif ($heuteErledigt): ?>
            <button class="checkin-button erledigt" disabled>Heute schon<br>erledigt ✔</button>
        <?php else: ?>
            <button class="checkin-button erledigt" disabled>Rückfall heute<br>vermerkt 💔</button>
        <?php endif; ?>

        <?php if ($naechsteStufe): ?>
            <p class="muted" style="margin-top:14px;">
                Noch <strong><?= (int) $naechsteStufe['fehlende_tage'] ?></strong> Tag(e) bis
                <?= h($naechsteStufe['icon']) ?> „<?= h($naechsteStufe['label']) ?>" (dann +<?= (int) $naechsteStufe['punkte'] ?> Punkte/Tag).
            </p>
        <?php endif; ?>
        <?php if ($naechsterMeilenstein): ?>
            <p class="muted">
                Noch <strong><?= (int) $naechsterMeilenstein['fehlende_tage'] ?></strong> Tag(e)
                bis zum Meilenstein „<?= h($naechsterMeilenstein['label']) ?>" (+<?= (int) $naechsterMeilenstein['punkte'] ?> Bonuspunkte).
            </p>
        <?php else: ?>
            <p class="muted">Alle Meilensteine erreicht – du bist ein echter Trockenheld! 🏆</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Dein Level</h3>
        <div class="level-kopf">
            <div class="level-badge">Lvl <?= (int) $level['level'] ?></div>
            <div>
                <div class="level-titel"><?= h($level['titel']) ?></div>
                <div class="hilfetext"><?= (int) $benutzer['punkte'] ?> Punkte gesamt</div>
            </div>
        </div>
        <?php if (!$level['ist_max_level']): ?>
            <div class="level-bar">
                <div class="level-bar-fuellung" style="width: <?= (int) $level['fortschritt_prozent'] ?>%;"></div>
            </div>
            <p class="hilfetext">Noch <?= (int) $level['fehlende_punkte'] ?> Punkte bis „<?= h($level['naechster_titel']) ?>"</p>
        <?php else: ?>
            <p class="hilfetext">Maximales Level erreicht – Respekt! 👑</p>
        <?php endif; ?>

        <div class="profil-stats" style="margin-top:22px;">
            <div>
                <div class="stat-wert"><?= (int) $benutzer['laengster_streak'] ?></div>
                <div class="stat-label">Längste Serie</div>
            </div>
            <div>
                <div class="stat-wert"><?= tage_seit($benutzer['start_datum']) ?></div>
                <div class="stat-label">Tage dabei seit Start</div>
            </div>
        </div>
        <p style="margin-top:20px;">
            <a href="profil.php?id=<?= (int) $benutzer['id'] ?>" class="btn btn-secondary">Mein öffentliches Profil ansehen</a>
        </p>
    </div>
</div>

<?php if ($offeneBelohnungen): foreach ($offeneBelohnungen as $ob): ?>
    <div class="card" style="border-color: var(--amber);">
        <h3>🎁 Belohnung für „<?= h(MEILENSTEINE[$ob['typ']]['label']) ?>" eintragen</h3>
        <p class="muted">Du hast diesen Meilenstein erreicht – womit belohnst du dich?</p>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="aktion" value="belohnung_speichern">
            <input type="hidden" name="meilenstein_id" value="<?= (int) $ob['id'] ?>">
            <div class="feld" style="display:flex;gap:10px;">
                <input type="text" name="belohnung_text" placeholder="z.B. Kinoabend, neues Buch, Massage …" required style="flex:1;">
                <button type="submit" class="btn btn-primary">Speichern</button>
            </div>
        </form>
    </div>
<?php endforeach; endif; ?>

<div class="card">
    <h3>Punkte-Stufen (Combo-Bonus)</h3>
    <p class="muted">Je länger deine Serie hält, desto mehr Punkte gibt's pro Tag.</p>
    <div class="meilenstein-liste">
        <?php foreach (array_reverse(PUNKTE_STUFEN) as $stufe): $aktiv = (int) $benutzer['aktueller_streak'] >= $stufe['ab_tag']; ?>
            <div class="meilenstein <?= $aktiv ? 'erreicht' : '' ?>">
                <div class="icon"><?= h($stufe['icon']) ?></div>
                <div><?= h($stufe['label']) ?></div>
                <div class="punkte">+<?= (int) $stufe['punkte'] ?>/Tag</div>
                <div class="hilfetext">ab Tag <?= (int) $stufe['ab_tag'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card">
    <h3>Logbuch (letzte 6 Wochen)</h3>
    <div class="kalender">
        <?php foreach ($kalender as $datum => $ok):
            $istHeute = $datum === date('Y-m-d');
            $klasse = $ok ? 'ok' : ($istHeute ? '' : 'fehlt');
        ?>
            <div class="kalender-tag <?= $klasse ?>" title="<?= h($datum) ?>">
                <?= $ok ? '😊' : ($istHeute ? '·' : '💤') ?>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="hilfetext" style="margin-top:10px;">😊 = eingecheckt &nbsp; 💤 = kein Check-in erfolgt</p>
</div>

<div class="card">
    <h3>Deine Meilensteine</h3>
    <?php if (!$erreichteMeilensteine): ?>
        <p class="muted">Noch keine erreicht – dein erster Meilenstein ist „Eine Woche" nach 7 Tagen in Folge.</p>
    <?php else: ?>
        <div class="meilenstein-liste">
            <?php foreach ($erreichteMeilensteine as $m): ?>
                <div class="meilenstein erreicht">
                    <div class="icon">🏅</div>
                    <div><?= h(MEILENSTEINE[$m['typ']]['label']) ?></div>
                    <div class="punkte">+<?= (int) $m['punkte_erhalten'] ?> Punkte</div>
                    <div class="hilfetext"><?= h($m['erreicht_am']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
