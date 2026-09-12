<?php
declare(strict_types=1);
require __DIR__ . '/../../src/bootstrap.php';

$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = Payments::verifyWebhookSignature($payload, $signature);
} catch (Throwable $e) {
    http_response_code(400);
    echo 'Webhook-Signatur ungültig: ' . $e->getMessage();
    exit;
}

if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    Market::markPaidBySessionId($session->id, $session->payment_intent ?? null);
}

header('Content-Type: application/json');
echo json_encode(['received' => true]);
