<?php
declare(strict_types=1);

final class Market
{
    public const CATEGORIES = [
        'kuerbismarkt' => 'Kürbismarkt',
        'weihnachtsmarkt' => 'Weihnachtsmarkt',
        'wochenmarkt' => 'Wochenmarkt',
        'flohmarkt' => 'Flohmarkt',
        'bauernmarkt' => 'Bauernmarkt',
        'sonstiges' => 'Sonstiges',
    ];

    private const PUBLIC_COLUMNS = 'id, name, category, description, street, postal_code AS postalCode,
        city, latitude, longitude, start_date AS startDate, end_date AS endDate,
        opening_hours AS openingHours, website, created_at AS createdAt';

    /** Oeffentliches Verzeichnis: nur redaktionell veroeffentlichte Eintraege. */
    public static function publicList(array $filters): array
    {
        $sql = 'SELECT ' . self::PUBLIC_COLUMNS . ' FROM markets WHERE status = "published"';
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= ' AND category = :category';
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['city'])) {
            $sql .= ' AND city LIKE :city';
            $params['city'] = '%' . $filters['city'] . '%';
        }
        if (!empty($filters['from'])) {
            $sql .= ' AND end_date >= :from';
            $params['from'] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $sql .= ' AND start_date <= :to';
            $params['to'] = $filters['to'];
        }
        if (!empty($filters['q'])) {
            $sql .= ' AND (name LIKE :q OR description LIKE :q OR city LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        $sql .= ' ORDER BY start_date ASC';

        $stmt = Database::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function publicFind(int $id): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT ' . self::PUBLIC_COLUMNS . ' FROM markets WHERE id = :id AND status = "published"'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Legt einen neuen, noch unbezahlten Eintrag an. Gibt die neue ID zurueck. */
    public static function create(array $data): int
    {
        $stmt = Database::get()->prepare('INSERT INTO markets
            (name, category, description, street, postal_code, city, latitude, longitude,
             start_date, end_date, opening_hours, website, contact_name, contact_email,
             price_amount, price_currency, payment_status, status)
            VALUES
            (:name, :category, :description, :street, :postalCode, :city, :latitude, :longitude,
             :startDate, :endDate, :openingHours, :website, :contactName, :contactEmail,
             :priceAmount, :priceCurrency, "pending", "pending_payment")');

        $stmt->execute([
            'name' => $data['name'],
            'category' => $data['category'],
            'description' => $data['description'],
            'street' => $data['street'],
            'postalCode' => $data['postalCode'],
            'city' => $data['city'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'startDate' => $data['startDate'],
            'endDate' => $data['endDate'],
            'openingHours' => $data['openingHours'] !== '' ? $data['openingHours'] : null,
            'website' => $data['website'] !== '' ? $data['website'] : null,
            'contactName' => $data['contactName'],
            'contactEmail' => $data['contactEmail'],
            'priceAmount' => $data['priceAmount'],
            'priceCurrency' => $data['priceCurrency'],
        ]);

        return (int) Database::get()->lastInsertId();
    }

    public static function findRaw(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM markets WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function setStripeSession(int $id, string $sessionId): void
    {
        $stmt = Database::get()->prepare('UPDATE markets SET stripe_session_id = :sid WHERE id = :id');
        $stmt->execute(['sid' => $sessionId, 'id' => $id]);
    }

    /** Wird ausschliesslich vom Stripe-Webhook aufgerufen, sobald eine Zahlung bestaetigt ist. */
    public static function markPaidBySessionId(string $sessionId, ?string $paymentIntentId): void
    {
        $stmt = Database::get()->prepare('UPDATE markets
            SET payment_status = "paid", status = "pending_review", stripe_payment_intent_id = :pi
            WHERE stripe_session_id = :sid AND status = "pending_payment"');
        $stmt->execute(['pi' => $paymentIntentId, 'sid' => $sessionId]);
    }

    public static function adminList(?string $status): array
    {
        if ($status !== null && $status !== '') {
            $stmt = Database::get()->prepare('SELECT * FROM markets WHERE status = :status ORDER BY created_at DESC');
            $stmt->execute(['status' => $status]);
        } else {
            $stmt = Database::get()->query('SELECT * FROM markets ORDER BY created_at DESC');
        }
        return $stmt->fetchAll();
    }

    /**
     * Veroeffentlicht oder lehnt einen Eintrag ab. Erzwingt serverseitig, dass nur bezahlte
     * Eintraege veroeffentlicht werden koennen - es gibt keinen kostenlosen Pfad.
     */
    public static function updateStatus(int $id, string $status, ?string $rejectionReason): bool
    {
        $market = self::findRaw($id);
        if ($market === null) {
            return false;
        }
        if ($market['payment_status'] !== 'paid') {
            throw new RuntimeException('Eintrag wurde noch nicht bezahlt und kann nicht veroeffentlicht werden.');
        }

        $stmt = Database::get()->prepare(
            'UPDATE markets SET status = :status, rejection_reason = :reason WHERE id = :id'
        );
        $stmt->execute([
            'status' => $status,
            'reason' => $status === 'rejected' ? $rejectionReason : null,
            'id' => $id,
        ]);
        return true;
    }

    public static function delete(int $id): void
    {
        $stmt = Database::get()->prepare('DELETE FROM markets WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
