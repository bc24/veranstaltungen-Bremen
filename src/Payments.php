<?php
declare(strict_types=1);

use Stripe\StripeClient;
use Stripe\Webhook;

final class Payments
{
    private static function client(): StripeClient
    {
        return new StripeClient(env('STRIPE_SECRET_KEY', ''));
    }

    /**
     * Erstellt eine Stripe-Checkout-Session fuer die einmalige Listing-Gebuehr.
     * Es gibt bewusst keinen kostenlosen Pfad: ohne erfolgreiche Zahlung bleibt der
     * Eintrag dauerhaft im Status "pending_payment" und wird nie veroeffentlicht.
     */
    public static function createCheckoutSession(array $market): string
    {
        $appUrl = rtrim(env('APP_URL', 'http://localhost:8080'), '/');

        $session = self::client()->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $market['price_currency'],
                    'unit_amount' => (int) $market['price_amount'],
                    'product_data' => [
                        'name' => 'Markteintrag: ' . $market['name'],
                        'description' => 'Einmalige Gebühr für die kuratierte Aufnahme in das Marktverzeichnis.',
                    ],
                ],
                'quantity' => 1,
            ]],
            'metadata' => ['marketId' => (string) $market['id']],
            'success_url' => $appUrl . '/einreichen-erfolg.php?market_id=' . $market['id'],
            'cancel_url' => $appUrl . '/einreichen-abgebrochen.php?market_id=' . $market['id'],
        ]);

        Market::setStripeSession((int) $market['id'], $session->id);

        return $session->url;
    }

    public static function verifyWebhookSignature(string $payload, string $signature): \Stripe\Event
    {
        return Webhook::constructEvent($payload, $signature, env('STRIPE_WEBHOOK_SECRET', ''));
    }
}
