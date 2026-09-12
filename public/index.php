<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$filters = [
    'q' => trim((string) ($_GET['q'] ?? '')),
    'category' => trim((string) ($_GET['category'] ?? '')),
    'city' => trim((string) ($_GET['city'] ?? '')),
    'from' => trim((string) ($_GET['from'] ?? '')),
    'to' => trim((string) ($_GET['to'] ?? '')),
];

$markets = Market::publicList($filters);

$pageTitle = 'Marktverzeichnis';
require __DIR__ . '/partials/header.php';
?>

<form class="filters-bar" method="get" action="/index.php">
  <input class="filters-search" type="text" name="q" placeholder="Suche nach Name, Ort, Beschreibung …"
         value="<?= htmlspecialchars($filters['q']) ?>" />
  <select name="category">
    <option value="">Alle Kategorien</option>
    <?php foreach (Market::CATEGORIES as $value => $label): ?>
      <option value="<?= htmlspecialchars($value) ?>" <?= $filters['category'] === $value ? 'selected' : '' ?>>
        <?= htmlspecialchars($label) ?>
      </option>
    <?php endforeach; ?>
  </select>
  <input type="text" name="city" placeholder="Stadt" value="<?= htmlspecialchars($filters['city']) ?>" />
  <label class="filters-date">
    von
    <input type="date" name="from" value="<?= htmlspecialchars($filters['from']) ?>" />
  </label>
  <label class="filters-date">
    bis
    <input type="date" name="to" value="<?= htmlspecialchars($filters['to']) ?>" />
  </label>
  <button type="submit" class="btn-primary">Filtern</button>
  <?php if (array_filter($filters)): ?>
    <a href="/index.php" class="btn-secondary">Zurücksetzen</a>
  <?php endif; ?>
</form>

<div class="home-layout">
  <div class="market-list">
    <div class="market-list-count">
      <?= count($markets) ?> <?= count($markets) === 1 ? 'Markt' : 'Märkte' ?> gefunden
    </div>

    <?php if (empty($markets)): ?>
      <div class="empty-box">
        <p>Keine Märkte gefunden. Ändere die Filter oder trage selbst einen Markt ein.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($markets as $m): ?>
      <div class="market-card" id="market-<?= (int) $m['id'] ?>">
        <span class="category-badge category-<?= htmlspecialchars($m['category']) ?>">
          <?= htmlspecialchars(Market::CATEGORIES[$m['category']] ?? $m['category']) ?>
        </span>
        <h3><?= htmlspecialchars($m['name']) ?></h3>
        <p class="market-card-desc"><?= htmlspecialchars($m['description']) ?></p>
        <div class="market-card-meta">
          <div>📅
            <?= htmlspecialchars(date('d.m.Y', strtotime($m['startDate']))) ?>
            <?php if ($m['startDate'] !== $m['endDate']): ?>
              – <?= htmlspecialchars(date('d.m.Y', strtotime($m['endDate']))) ?>
            <?php endif; ?>
          </div>
          <div>📍 <?= htmlspecialchars($m['street']) ?>, <?= htmlspecialchars($m['postalCode']) ?> <?= htmlspecialchars($m['city']) ?></div>
          <?php if (!empty($m['openingHours'])): ?>
            <div>🕒 <?= htmlspecialchars($m['openingHours']) ?></div>
          <?php endif; ?>
          <?php if (!empty($m['website'])): ?>
            <div>🔗 <a href="<?= htmlspecialchars($m['website']) ?>" target="_blank" rel="noopener noreferrer">Website</a></div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="market-map">
    <div id="map"></div>
  </div>
</div>

<script>
  window.MARKETS = <?= json_encode(array_map(static fn($m) => [
      'id' => (int) $m['id'],
      'name' => $m['name'],
      'category' => $m['category'],
      'categoryLabel' => Market::CATEGORIES[$m['category']] ?? $m['category'],
      'street' => $m['street'],
      'postalCode' => $m['postalCode'],
      'city' => $m['city'],
      'latitude' => (float) $m['latitude'],
      'longitude' => (float) $m['longitude'],
  ], $markets), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS) ?>;
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="/assets/map.js"></script>

<?php require __DIR__ . '/partials/footer.php'; ?>
