<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

$db = Database::get();

$adminEmail = env('ADMIN_EMAIL');
$adminPassword = env('ADMIN_PASSWORD');
if (!$adminEmail || !$adminPassword) {
    fwrite(STDERR, "ADMIN_EMAIL und ADMIN_PASSWORD muessen in .env gesetzt sein.\n");
    exit(1);
}

$stmt = $db->prepare('SELECT id FROM admins WHERE email = :email');
$stmt->execute(['email' => $adminEmail]);
if ($stmt->fetch()) {
    echo "Admin-Konto existiert bereits: $adminEmail\n";
} else {
    $stmt = $db->prepare('INSERT INTO admins (email, password_hash) VALUES (:email, :hash)');
    $stmt->execute(['email' => $adminEmail, 'hash' => password_hash($adminPassword, PASSWORD_DEFAULT)]);
    echo "Admin-Konto angelegt: $adminEmail\n";
}

$count = (int) $db->query('SELECT COUNT(*) AS c FROM markets')->fetch()['c'];
if ($count > 0) {
    echo "Markteintraege existieren bereits, ueberspringe Beispieldaten.\n";
    exit(0);
}

$samples = [
    [
        'name' => 'Bremer Kürbismarkt Findorff', 'category' => 'kuerbismarkt',
        'description' => 'Über 100 Kürbissorten, Kürbis-Schnitzstand für Kinder und regionale Herbstspezialitäten.',
        'street' => 'Nordstr. 12', 'postal_code' => '28217', 'city' => 'Bremen',
        'latitude' => 53.0951, 'longitude' => 8.7869,
        'start_date' => '2026-10-03', 'end_date' => '2026-10-31',
        'opening_hours' => 'Mo-So 10:00-18:00', 'website' => 'https://example.org/kuerbismarkt-findorff',
        'contact_name' => 'Anke Meyer', 'contact_email' => 'info@kuerbismarkt-findorff.example',
        'status' => 'published',
    ],
    [
        'name' => 'Weihnachtsmarkt Schlachte', 'category' => 'weihnachtsmarkt',
        'description' => 'Historischer Weihnachtsmarkt direkt an der Weser mit Glühwein, Kunsthandwerk und Live-Musik.',
        'street' => 'Schlachte', 'postal_code' => '28195', 'city' => 'Bremen',
        'latitude' => 53.0757, 'longitude' => 8.8017,
        'start_date' => '2026-11-23', 'end_date' => '2026-12-30',
        'opening_hours' => 'Mo-So 11:00-21:00', 'website' => 'https://example.org/weihnachtsmarkt-schlachte',
        'contact_name' => 'Jan Hendricks', 'contact_email' => 'kontakt@schlachte-weihnacht.example',
        'status' => 'published',
    ],
    [
        'name' => 'Kürbisfest Werderland', 'category' => 'kuerbismarkt',
        'description' => 'Kürbisfeld zum Selberpflücken, Traktorfahrten und Hofcafé.',
        'street' => 'Werderlandstr. 5', 'postal_code' => '28757', 'city' => 'Bremen',
        'latitude' => 53.1685, 'longitude' => 8.6423,
        'start_date' => '2026-09-26', 'end_date' => '2026-11-01',
        'opening_hours' => 'Sa-So 09:00-17:00', 'website' => null,
        'contact_name' => 'Frieda Boelken', 'contact_email' => 'hof@werderland.example',
        'status' => 'pending_review',
    ],
];

$stmt = $db->prepare('INSERT INTO markets
    (name, category, description, street, postal_code, city, latitude, longitude,
     start_date, end_date, opening_hours, website, contact_name, contact_email,
     price_amount, price_currency, payment_status, stripe_session_id, status)
    VALUES
    (:name, :category, :description, :street, :postal_code, :city, :latitude, :longitude,
     :start_date, :end_date, :opening_hours, :website, :contact_name, :contact_email,
     :price_amount, :price_currency, "paid", :stripe_session_id, :status)');

foreach ($samples as $i => $sample) {
    $stmt->execute(array_merge($sample, [
        'price_amount' => (int) env('LISTING_PRICE_AMOUNT', '4900'),
        'price_currency' => env('LISTING_CURRENCY', 'eur'),
        'stripe_session_id' => 'seed_demo_session_' . ($i + 1),
    ]));
}

echo "Beispiel-Markteinträge angelegt.\n";
