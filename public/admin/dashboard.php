<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

Auth::requireLogin();

const STATUS_LABELS = [
    'pending_payment' => 'Zahlung ausstehend',
    'pending_review' => 'Prüfung ausstehend',
    'published' => 'Veröffentlicht',
    'rejected' => 'Abgelehnt',
];

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::checkCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sicherheitsprüfung fehlgeschlagen. Bitte erneut versuchen.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        $id = (int) ($_POST['id'] ?? 0);

        try {
            if ($action === 'publish') {
                Market::updateStatus($id, 'published', null);
                $message = 'Eintrag veröffentlicht.';
            } elseif ($action === 'reject') {
                Market::updateStatus($id, 'rejected', trim((string) ($_POST['reason'] ?? '')));
                $message = 'Eintrag abgelehnt.';
            } elseif ($action === 'delete') {
                Market::delete($id);
                $message = 'Eintrag gelöscht.';
            }
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$statusFilter = (string) ($_GET['status'] ?? 'pending_review');
$markets = Market::adminList($statusFilter !== '' ? $statusFilter : null);

$pageTitle = 'Admin-Kuratierung';
require __DIR__ . '/../partials/header.php';
?>

<div class="admin-dashboard">
  <div class="admin-dashboard-header">
    <h1>Kuratierung</h1>
    <a href="/admin/logout.php" class="btn-secondary">Abmelden</a>
  </div>

  <?php if ($message): ?>
    <div class="success-box" style="margin-bottom:16px;"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="error-box" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="admin-filter-bar">
    <?php foreach (STATUS_LABELS as $value => $label): ?>
      <a class="filter-btn <?= $statusFilter === $value ? 'filter-btn-active' : '' ?>"
         href="/admin/dashboard.php?status=<?= urlencode($value) ?>"><?= htmlspecialchars($label) ?></a>
    <?php endforeach; ?>
    <a class="filter-btn <?= $statusFilter === '' ? 'filter-btn-active' : '' ?>" href="/admin/dashboard.php?status=">Alle</a>
  </div>

  <?php if (empty($markets)): ?>
    <p>Keine Einträge in diesem Status.</p>
  <?php endif; ?>

  <div class="admin-market-list">
    <?php foreach ($markets as $m): ?>
      <div class="admin-market-row">
        <div>
          <strong><?= htmlspecialchars($m['name']) ?></strong> &ndash;
          <?= htmlspecialchars(Market::CATEGORIES[$m['category']] ?? $m['category']) ?>
          <div class="admin-market-meta">
            <?= htmlspecialchars($m['street']) ?>, <?= htmlspecialchars($m['postal_code']) ?> <?= htmlspecialchars($m['city']) ?>
            · <?= htmlspecialchars($m['start_date']) ?> bis <?= htmlspecialchars($m['end_date']) ?>
          </div>
          <div class="admin-market-meta">
            Kontakt: <?= htmlspecialchars($m['contact_name']) ?> (<?= htmlspecialchars($m['contact_email']) ?>)
            · Zahlung: <?= htmlspecialchars($m['payment_status']) ?>
            · Status: <?= htmlspecialchars(STATUS_LABELS[$m['status']] ?? $m['status']) ?>
          </div>
          <?php if (!empty($m['rejection_reason'])): ?>
            <div class="admin-market-meta">Ablehnungsgrund: <?= htmlspecialchars($m['rejection_reason']) ?></div>
          <?php endif; ?>
        </div>
        <div class="admin-market-actions">
          <?php if ($m['status'] !== 'published' && $m['payment_status'] === 'paid'): ?>
            <form method="post" action="/admin/dashboard.php?status=<?= urlencode($statusFilter) ?>">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken()) ?>" />
              <input type="hidden" name="action" value="publish" />
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>" />
              <button type="submit" class="btn-primary">Veröffentlichen</button>
            </form>
          <?php endif; ?>
          <?php if ($m['status'] !== 'rejected'): ?>
            <form method="post" action="/admin/dashboard.php?status=<?= urlencode($statusFilter) ?>"
                  onsubmit="var r=prompt('Grund der Ablehnung (optional):',''); if(r===null) return false; this.reason.value=r; return true;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken()) ?>" />
              <input type="hidden" name="action" value="reject" />
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>" />
              <input type="hidden" name="reason" value="" />
              <button type="submit" class="btn-secondary">Ablehnen</button>
            </form>
          <?php endif; ?>
          <form method="post" action="/admin/dashboard.php?status=<?= urlencode($statusFilter) ?>"
                onsubmit="return confirm('Diesen Eintrag endgültig löschen?');">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken()) ?>" />
            <input type="hidden" name="action" value="delete" />
            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>" />
            <button type="submit" class="btn-danger">Löschen</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
