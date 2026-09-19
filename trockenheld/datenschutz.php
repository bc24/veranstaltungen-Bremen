<?php
require_once __DIR__ . '/includes/functions.php';
$seitentitel = 'Datenschutzerklärung';
require __DIR__ . '/includes/header.php';
?>

<div class="card form-schmal">
    <h1>Datenschutzerklärung</h1>
    <p class="muted">Stand: <?= date('d.m.Y') ?></p>

    <h3>1. Verantwortlicher</h3>
    <p>
        Verantwortlich für die Datenverarbeitung auf dieser Website (<?= h(APP_NAME) ?>) ist:<br><br>
        Frank Panzer<br>
        Kreinsloger 103<br>
        28777 Bremen<br>
        E-Mail: <a href="mailto:frank@panzerit.de">frank@panzerit.de</a>
    </p>

    <h3>2. Hosting</h3>
    <p>
        Diese Website wird auf eigener Serverinfrastruktur des Verantwortlichen betrieben
        (Selbsthosting, Serverstandort Deutschland). Die öffentliche IP-Anbindung erfolgt über den
        Internet-Anbieter Webtropia. Beim Aufruf der Seite verarbeitet der Server automatisch
        technisch notwendige Verbindungsdaten (u. a. IP-Adresse, Datum und Uhrzeit des Zugriffs,
        aufgerufene Seite, verwendeter Browser) in sogenannten Server-Logfiles. Rechtsgrundlage ist
        Art. 6 Abs. 1 lit. f DSGVO (berechtigtes Interesse an einem sicheren und funktionsfähigen
        Betrieb der Website). Die Logfiles werden regelmäßig gelöscht bzw. anonymisiert.
    </p>

    <h3>3. Registrierung und Nutzerkonto</h3>
    <p>
        Für die Nutzung von <?= h(APP_NAME) ?> ist eine Registrierung erforderlich. Dabei werden
        folgende Daten erhoben und gespeichert: Benutzername, E-Mail-Adresse, ein sicheres
        Passwort-Hash (das Passwort selbst wird nicht im Klartext gespeichert) sowie das von dir
        angegebene Startdatum deiner Challenge. Optional kannst du weitere Profilangaben ergänzen
        (Anzeigename, Wohnort, Geburtsjahr, „Über mich"-Text, persönliches Ziel).
    </p>
    <p>
        Rechtsgrundlage ist Art. 6 Abs. 1 lit. b DSGVO (Erfüllung eines Vertrags bzw. Durchführung
        vorvertraglicher Maßnahmen – Bereitstellung des Nutzerkontos und der Challenge-Funktionen).
    </p>

    <h3>4. Tägliche Check-ins, Streaks und Meilensteine</h3>
    <p>
        Jeder von dir bestätigte Check-in ("heute nichts getrunken") wird mit Datum und optionaler
        Notiz gespeichert, um deinen Streak, deine Bonuspunkte und erreichte Meilensteine zu
        berechnen und dir in deinem Logbuch anzuzeigen. Rechtsgrundlage ist Art. 6 Abs. 1 lit. b
        DSGVO (Vertragserfüllung – dies ist die Kernfunktion der App).
    </p>

    <h3>5. Öffentliches Profil</h3>
    <p>
        Du kannst in den Einstellungen wählen, ob dein Profil (Anzeigename, Streak-Statistik,
        Meilensteine, ggf. „Über mich"-Text) für andere angemeldete und nicht angemeldete Besucher
        öffentlich sichtbar ist, oder ob es privat bleibt. Rechtsgrundlage bei aktivierter
        Sichtbarkeit ist deine Einwilligung (Art. 6 Abs. 1 lit. a DSGVO). Du kannst diese Einstellung
        jederzeit in deinem Profil ändern.
    </p>

    <h3>6. Erinnerungs-E-Mails</h3>
    <p>
        Wenn du die Funktion „E-Mail-Erinnerung" aktivierst, erhältst du eine tägliche E-Mail an die
        von dir hinterlegte Adresse, falls du dich an dem Tag noch nicht eingecheckt hast.
        Rechtsgrundlage ist deine Einwilligung (Art. 6 Abs. 1 lit. a DSGVO). Du kannst diese
        Einwilligung jederzeit widerrufen, indem du die Erinnerung in deinen Einstellungen
        deaktivierst.
    </p>

    <h3>7. Cookies / Sitzungsverwaltung</h3>
    <p>
        <?= h(APP_NAME) ?> verwendet ausschließlich ein technisch notwendiges Session-Cookie, um dich
        nach dem Login angemeldet zu halten. Es werden keine Tracking- oder Marketing-Cookies
        eingesetzt. Rechtsgrundlage ist Art. 6 Abs. 1 lit. f DSGVO (berechtigtes Interesse an einer
        funktionierenden Anmeldung). Das Cookie wird gelöscht, wenn du dich abmeldest oder deinen
        Browser schließt.
    </p>

    <h3>8. Speicherdauer</h3>
    <p>
        Deine Daten werden gespeichert, solange dein Nutzerkonto besteht. Wenn du die Löschung
        deines Kontos wünschst, wende dich an <a href="mailto:frank@panzerit.de">frank@panzerit.de</a>;
        deine Daten werden dann vollständig gelöscht, soweit keine gesetzlichen Aufbewahrungspflichten
        entgegenstehen.
    </p>

    <h3>9. Deine Rechte</h3>
    <p>Du hast jederzeit das Recht auf:</p>
    <ul>
        <li>Auskunft über die zu deiner Person gespeicherten Daten (Art. 15 DSGVO)</li>
        <li>Berichtigung unrichtiger Daten (Art. 16 DSGVO)</li>
        <li>Löschung deiner Daten (Art. 17 DSGVO)</li>
        <li>Einschränkung der Verarbeitung (Art. 18 DSGVO)</li>
        <li>Datenübertragbarkeit (Art. 20 DSGVO)</li>
        <li>Widerspruch gegen die Verarbeitung (Art. 21 DSGVO)</li>
        <li>Widerruf erteilter Einwilligungen mit Wirkung für die Zukunft (Art. 7 Abs. 3 DSGVO)</li>
    </ul>
    <p>
        Zur Ausübung deiner Rechte genügt eine E-Mail an
        <a href="mailto:frank@panzerit.de">frank@panzerit.de</a>. Du hast außerdem das Recht, dich bei
        einer Datenschutz-Aufsichtsbehörde zu beschweren, z. B. bei der für Bremen zuständigen
        Aufsichtsbehörde: Die Landesbeauftragte für Datenschutz und Informationsfreiheit der Freien
        Hansestadt Bremen.
    </p>

    <h3>10. Datensicherheit</h3>
    <p>
        Passwörter werden ausschließlich als sicherer Hash (nicht im Klartext) gespeichert. Wir
        setzen technische und organisatorische Maßnahmen ein, um deine Daten gegen Manipulation,
        Verlust und unberechtigten Zugriff zu schützen.
    </p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
