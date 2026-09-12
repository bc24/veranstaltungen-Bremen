<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$pageTitle = 'Zahlung erfolgreich';
require __DIR__ . '/partials/header.php';
?>

<div class="outcome-page">
  <h1>Zahlung erfolgreich ✅</h1>
  <p>
    Vielen Dank! Dein Markteintrag wurde bezahlt und liegt jetzt zur redaktionellen Prüfung vor.
    Nach der Freigabe erscheint er im öffentlichen Verzeichnis.
  </p>
  <a href="/index.php" class="btn-primary">Zurück zum Verzeichnis</a>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
