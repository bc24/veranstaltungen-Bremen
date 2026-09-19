<?php
/**
 * Stellt die PDO-Datenbankverbindung her.
 */

if (!file_exists(__DIR__ . '/../config.php')) {
    http_response_code(500);
    die('Konfigurationsdatei fehlt. Bitte config.example.php nach config.php kopieren und anpassen.');
}

require_once __DIR__ . '/../config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Datenbankverbindung fehlgeschlagen. Bitte die Zugangsdaten in config.php prüfen.');
}
