<?php
declare(strict_types=1);
require __DIR__ . '/../src/bootstrap.php';

$pageTitle = 'Zahlung abgebrochen';
require __DIR__ . '/partials/header.php';
?>

<div class="outcome-page">
  <h1>Zahlung abgebrochen</h1>
  <p>
    Die Zahlung wurde nicht abgeschlossen. Ohne erfolgreiche Zahlung kann der Eintrag nicht
    veröffentlicht werden &ndash; es gibt keine kostenlose Basisversion. Du kannst es jederzeit
    erneut versuchen.
  </p>
  <a href="/einreichen.php" class="btn-primary">Erneut versuchen</a>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
