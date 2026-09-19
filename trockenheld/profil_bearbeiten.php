<?php
require_once __DIR__ . '/includes/functions.php';
login_erzwingen();

$benutzer = aktueller_benutzer($pdo);
$fehler = [];
$erfolg = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_pruefen();
    $aktion = $_POST['aktion'] ?? 'profil';

    if ($aktion === 'profil') {
        $anzeigename = trim($_POST['anzeigename'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $warumText = trim($_POST['warum_text'] ?? '');
        $wohnort = trim($_POST['wohnort'] ?? '');
        $geburtsjahr = trim($_POST['geburtsjahr'] ?? '');
        $zielTage = trim($_POST['ziel_tage'] ?? '');
        $profilOeffentlich = isset($_POST['profil_oeffentlich']) ? 1 : 0;
        $emailErinnerung = isset($_POST['email_erinnerung']) ? 1 : 0;
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fehler[] = 'Bitte eine gültige E-Mail-Adresse angeben.';
        } else {
            $stmt = $pdo->prepare('SELECT 1 FROM benutzer WHERE email = ? AND id <> ?');
            $stmt->execute([$email, $benutzer['id']]);
            if ($stmt->fetchColumn()) {
                $fehler[] = 'Diese E-Mail-Adresse wird bereits von einem anderen Konto verwendet.';
            }
        }

        if (!$fehler) {
            $update = $pdo->prepare(
                'UPDATE benutzer SET anzeigename=?, bio=?, warum_text=?, wohnort=?, geburtsjahr=?, ziel_tage=?,
                     profil_oeffentlich=?, email_erinnerung=?, email=?
                 WHERE id=?'
            );
            $update->execute([
                $anzeigename !== '' ? $anzeigename : null,
                $bio !== '' ? $bio : null,
                $warumText !== '' ? $warumText : null,
                $wohnort !== '' ? $wohnort : null,
                $geburtsjahr !== '' ? (int) $geburtsjahr : null,
                $zielTage !== '' ? (int) $zielTage : null,
                $profilOeffentlich,
                $emailErinnerung,
                $email,
                $benutzer['id'],
            ]);
            flash_setzen('erfolg', 'Deine Einstellungen wurden gespeichert.');
            header('Location: profil_bearbeiten.php');
            exit;
        }
    }

    if ($aktion === 'passwort') {
        $aktuelles = $_POST['aktuelles_passwort'] ?? '';
        $neu = $_POST['neues_passwort'] ?? '';
        $neu2 = $_POST['neues_passwort_wiederholen'] ?? '';

        if (!password_verify($aktuelles, $benutzer['passwort_hash'])) {
            $fehler[] = 'Das aktuelle Passwort ist falsch.';
        } elseif (strlen($neu) < 8) {
            $fehler[] = 'Das neue Passwort muss mindestens 8 Zeichen lang sein.';
        } elseif ($neu !== $neu2) {
            $fehler[] = 'Die neuen Passwörter stimmen nicht überein.';
        } else {
            $update = $pdo->prepare('UPDATE benutzer SET passwort_hash=? WHERE id=?');
            $update->execute([password_hash($neu, PASSWORD_DEFAULT), $benutzer['id']]);
            flash_setzen('erfolg', 'Passwort erfolgreich geändert.');
            header('Location: profil_bearbeiten.php');
            exit;
        }
    }

    $benutzer = aktueller_benutzer($pdo);
}

$seitentitel = 'Einstellungen';
require __DIR__ . '/includes/header.php';
?>

<?php if ($fehler): ?>
    <div class="flash flash-fehler">
        <ul style="margin:0;padding-left:18px;">
            <?php foreach ($fehler as $f): ?><li><?= h($f) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card form-schmal">
    <h2>Profil &amp; Einstellungen</h2>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="aktion" value="profil">

        <div class="feld">
            <label for="anzeigename">Anzeigename</label>
            <input type="text" id="anzeigename" name="anzeigename" value="<?= h($benutzer['anzeigename']) ?>">
        </div>
        <div class="feld">
            <label for="email">E-Mail-Adresse</label>
            <input type="email" id="email" name="email" required value="<?= h($benutzer['email']) ?>">
        </div>
        <div class="feld">
            <label for="bio">Über mich</label>
            <textarea id="bio" name="bio"><?= h($benutzer['bio']) ?></textarea>
        </div>
        <div class="feld">
            <label for="warum_text">Warum machst du diese Challenge?</label>
            <textarea id="warum_text" name="warum_text"><?= h($benutzer['warum_text']) ?></textarea>
        </div>
        <div class="feld">
            <label for="wohnort">Wohnort</label>
            <input type="text" id="wohnort" name="wohnort" value="<?= h($benutzer['wohnort']) ?>">
        </div>
        <div class="feld">
            <label for="geburtsjahr">Geburtsjahr</label>
            <input type="number" id="geburtsjahr" name="geburtsjahr" min="1900" max="<?= date('Y') ?>" value="<?= h((string) $benutzer['geburtsjahr']) ?>">
        </div>
        <div class="feld">
            <label for="ziel_tage">Persönliches Ziel (Tage)</label>
            <input type="number" id="ziel_tage" name="ziel_tage" min="1" value="<?= h((string) $benutzer['ziel_tage']) ?>">
        </div>

        <div class="feld checkbox-feld">
            <input type="checkbox" id="profil_oeffentlich" name="profil_oeffentlich" <?= $benutzer['profil_oeffentlich'] ? 'checked' : '' ?>>
            <label for="profil_oeffentlich" style="font-weight:400;">Mein Profil ist für andere Mitglieder öffentlich sichtbar</label>
        </div>
        <div class="feld checkbox-feld">
            <input type="checkbox" id="email_erinnerung" name="email_erinnerung" <?= $benutzer['email_erinnerung'] ? 'checked' : '' ?>>
            <label for="email_erinnerung" style="font-weight:400;">Ich möchte täglich per E-Mail an den Check-in erinnert werden</label>
        </div>

        <button type="submit" class="btn btn-primary">Speichern</button>
    </form>
</div>

<div class="card form-schmal">
    <h2>Passwort ändern</h2>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="aktion" value="passwort">
        <div class="feld">
            <label for="aktuelles_passwort">Aktuelles Passwort</label>
            <input type="password" id="aktuelles_passwort" name="aktuelles_passwort" required>
        </div>
        <div class="feld">
            <label for="neues_passwort">Neues Passwort</label>
            <input type="password" id="neues_passwort" name="neues_passwort" required minlength="8">
        </div>
        <div class="feld">
            <label for="neues_passwort_wiederholen">Neues Passwort wiederholen</label>
            <input type="password" id="neues_passwort_wiederholen" name="neues_passwort_wiederholen" required minlength="8">
        </div>
        <button type="submit" class="btn btn-secondary">Passwort ändern</button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
