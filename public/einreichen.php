<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$errors = [];
$old = [
    'name' => '', 'category' => 'kuerbismarkt', 'description' => '',
    'street' => '', 'postalCode' => '', 'city' => '',
    'latitude' => '', 'longitude' => '', 'startDate' => '', 'endDate' => '',
    'openingHours' => '', 'website' => '', 'contactName' => '', 'contactEmail' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::checkCsrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sicherheitsprüfung fehlgeschlagen. Bitte Formular erneut absenden.';
    }

    foreach (array_keys($old) as $field) {
        $old[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    $required = ['name', 'category', 'description', 'street', 'postalCode', 'city',
        'latitude', 'longitude', 'startDate', 'endDate', 'contactName', 'contactEmail'];
    foreach ($required as $field) {
        if ($old[$field] === '') {
            $errors[] = "Pflichtfeld fehlt: $field";
        }
    }
    if (!array_key_exists($old['category'], Market::CATEGORIES)) {
        $errors[] = 'Ungültige Kategorie.';
    }
    if ($old['contactEmail'] !== '' && !filter_var($old['contactEmail'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ungültige Kontakt-E-Mail-Adresse.';
    }
    if ($old['latitude'] !== '' && !is_numeric($old['latitude'])) {
        $errors[] = 'Breitengrad muss eine Zahl sein.';
    }
    if ($old['longitude'] !== '' && !is_numeric($old['longitude'])) {
        $errors[] = 'Längengrad muss eine Zahl sein.';
    }

    if (empty($errors)) {
        try {
            $marketId = Market::create([
                'name' => $old['name'],
                'category' => $old['category'],
                'description' => $old['description'],
                'street' => $old['street'],
                'postalCode' => $old['postalCode'],
                'city' => $old['city'],
                'latitude' => (float) $old['latitude'],
                'longitude' => (float) $old['longitude'],
                'startDate' => $old['startDate'],
                'endDate' => $old['endDate'],
                'openingHours' => $old['openingHours'],
                'website' => $old['website'],
                'contactName' => $old['contactName'],
                'contactEmail' => $old['contactEmail'],
                'priceAmount' => (int) env('LISTING_PRICE_AMOUNT', '4900'),
                'priceCurrency' => env('LISTING_CURRENCY', 'eur'),
            ]);

            $market = Market::findRaw($marketId);
            $checkoutUrl = Payments::createCheckoutSession($market);

            header('Location: ' . $checkoutUrl);
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Fehler bei der Verarbeitung: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Markt eintragen';
require __DIR__ . '/partials/header.php';
?>

<div class="submit-page">
  <h1>Markt eintragen</h1>
  <p class="submit-intro">
    Alle Einträge in diesem Verzeichnis sind kostenpflichtig &ndash; es gibt keine kostenlose
    Basisversion. Nach der Zahlung wird dein Eintrag redaktionell geprüft und danach veröffentlicht.
  </p>

  <?php if (!empty($errors)): ?>
    <div class="error-box" style="margin-bottom:16px;">
      <?php foreach ($errors as $error): ?>
        <div><?= htmlspecialchars($error) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" action="/einreichen.php" class="submit-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken()) ?>" />

    <label>
      Name des Marktes *
      <input required name="name" value="<?= htmlspecialchars($old['name']) ?>" />
    </label>

    <label>
      Kategorie *
      <select name="category">
        <?php foreach (Market::CATEGORIES as $value => $label): ?>
          <option value="<?= htmlspecialchars($value) ?>" <?= $old['category'] === $value ? 'selected' : '' ?>>
            <?= htmlspecialchars($label) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>
      Beschreibung *
      <textarea required name="description" rows="4"><?= htmlspecialchars($old['description']) ?></textarea>
    </label>

    <div class="form-row">
      <label>
        Straße &amp; Hausnummer *
        <input required name="street" value="<?= htmlspecialchars($old['street']) ?>" />
      </label>
      <label>
        PLZ *
        <input required name="postalCode" value="<?= htmlspecialchars($old['postalCode']) ?>" />
      </label>
      <label>
        Stadt *
        <input required name="city" value="<?= htmlspecialchars($old['city']) ?>" />
      </label>
    </div>

    <div class="form-row">
      <label>
        Breitengrad (Latitude) *
        <input required type="number" step="any" name="latitude" placeholder="z. B. 53.0793"
               value="<?= htmlspecialchars($old['latitude']) ?>" />
      </label>
      <label>
        Längengrad (Longitude) *
        <input required type="number" step="any" name="longitude" placeholder="z. B. 8.8017"
               value="<?= htmlspecialchars($old['longitude']) ?>" />
      </label>
    </div>
    <p class="form-hint">
      Koordinaten findest du z. B. über
      <a href="https://www.openstreetmap.org" target="_blank" rel="noopener noreferrer">OpenStreetMap</a>
      (Rechtsklick auf den Standort → „Was ist hier?“).
    </p>

    <div class="form-row">
      <label>
        Beginn *
        <input required type="date" name="startDate" value="<?= htmlspecialchars($old['startDate']) ?>" />
      </label>
      <label>
        Ende *
        <input required type="date" name="endDate" value="<?= htmlspecialchars($old['endDate']) ?>" />
      </label>
    </div>

    <label>
      Öffnungszeiten
      <input name="openingHours" placeholder="z. B. Mo–So 10:00–18:00" value="<?= htmlspecialchars($old['openingHours']) ?>" />
    </label>

    <label>
      Website
      <input type="url" name="website" placeholder="https://…" value="<?= htmlspecialchars($old['website']) ?>" />
    </label>

    <div class="form-row">
      <label>
        Ansprechpartner *
        <input required name="contactName" value="<?= htmlspecialchars($old['contactName']) ?>" />
      </label>
      <label>
        Kontakt-E-Mail *
        <input required type="email" name="contactEmail" value="<?= htmlspecialchars($old['contactEmail']) ?>" />
      </label>
    </div>

    <button type="submit" class="btn-primary">Weiter zur kostenpflichtigen Anmeldung</button>
  </form>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
