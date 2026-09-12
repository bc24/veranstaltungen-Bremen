<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$filters = [
    'q' => trim((string) ($_GET['q'] ?? '')),
    'category' => trim((string) ($_GET['category'] ?? '')),
    'city' => trim((string) ($_GET['city'] ?? '')),
    'from' => trim((string) ($_GET['from'] ?? '')),
    'to' => trim((string) ($_GET['to'] ?? '')),
];

echo json_encode(Market::publicList($filters), JSON_UNESCAPED_UNICODE);
