<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

if (Auth::isLoggedIn()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::checkCsrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sicherheitsprüfung fehlgeschlagen. Bitte erneut versuchen.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (Auth::attempt($email, $password)) {
            header('Location: /admin/dashboard.php');
            exit;
        }
        $error = 'Ungültige Anmeldedaten.';
    }
}

$pageTitle = 'Admin-Login';
require __DIR__ . '/../partials/header.php';
?>

<div class="admin-login-page">
  <h1>Admin-Login</h1>

  <?php if ($error): ?>
    <div class="error-box" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" action="/admin/login.php" class="submit-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Auth::csrfToken()) ?>" />
    <label>
      E-Mail
      <input required type="email" name="email" />
    </label>
    <label>
      Passwort
      <input required type="password" name="password" />
    </label>
    <button type="submit" class="btn-primary">Anmelden</button>
  </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
