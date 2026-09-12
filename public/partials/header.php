<?php
/** @var string $pageTitle */
$pageTitle = $pageTitle ?? 'Marktverzeichnis';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
  <link rel="stylesheet" href="/assets/style.css" />
</head>
<body>
<div class="app-shell">
  <header class="site-header">
    <div class="site-header-inner">
      <a href="/" class="brand">
        <span class="brand-emoji" aria-hidden="true">🎪</span>
        <div>
          <div class="brand-title">Marktverzeichnis</div>
          <div class="brand-subtitle">Kuratierte Kürbis-, Weihnachts- &amp; Wochenmärkte</div>
        </div>
      </a>
      <nav class="main-nav">
        <a href="/">Verzeichnis</a>
        <a href="/einreichen.php">Markt eintragen</a>
        <a href="/admin/dashboard.php">Admin</a>
      </nav>
    </div>
  </header>
  <main>
