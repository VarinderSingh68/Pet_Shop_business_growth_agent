<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use App\Models\User;

/**
 * Business logic for the delivery-partner (rider) Android app: login,
 * assignment lookup, status transitions, and location pings. Kept separate
 * from DeliveryController so the HTTP layer stays thin, matching the rest
 * of app/Services.
 */
final class DeliveryService
{
    public function __construct(
        private readonly ApiTokenService $tokens = new ApiTokenService(),
    ) {
    }

    /**
     * @return array{token: string, partner: array<string, mixed>}|null null on bad credentials,
     * wrong role, or an inactive account
     */
    public function login(string $email, string $password, ?string $deviceName = null): ?array
    {
        $user = User::findByEmail($email);

        if ($user === null || !User::verifyPassword($user, $password) || (int) $user['is_active'] === 0) {
            return null;
        }

        $role = Database::instance()->selectOne('SELECT slug FROM roles WHERE id = :id', ['id' => $user['role_id']]);
        if ($role === null || $role['slug'] !== 'delivery') {
            return null;
        }

        $name = 'Delivery app' . ($deviceName !== null && $deviceName !== '' ? " ({$deviceName})" : '');
        // Riders ping /location every 20-30s while a delivery is active, so
        // the default 60/min integration-token limit is too tight.
        $result = $this->tokens->create((int) $user['id'], $name, 120);

        return ['token' => $result['token'], 'partner' => $user];
    }

    public function register(string $name, string $email, string $password, ?string $deviceName = null): ?array
    {
        $db = Database::instance();
        $existing = User::findByEmail($email);
        if ($existing !== null) {
            throw new \RuntimeException('This email is already registered.');
        }

        $role = $db->selectOne('SELECT id FROM roles WHERE slug = :slug', ['slug' => 'delivery']);
        if ($role === null) {
            throw new \RuntimeException('Delivery role not found in system.');
        }

        $userId = User::create([
            'name' => $name,
            'email' => strtolower($email),
            'password_hash' => User::hashPassword($password),
            'role_id' => (int) $role['id'],
            'is_active' => 1,
        ]);

        if (!$userId) {
            return null;
        }

        $tokenName = 'Delivery app' . ($deviceName !== null && $deviceName !== '' ? " ({$deviceName})" : '');
        $result = $this->tokens->create($userId, $tokenName, 120);

        return [
            'token' => $result['token'],
            'partner' => User::find($userId),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function assignedOrders(int $partnerId, ?string $status = null): array
    {
        return DeliveryAssignment::forPartner($partnerId, $status);
    }

    /** @return array<string, mixed>|null null if this order isn't assigned to this rider */
    public function orderForPartner(int $partnerId, int $orderId): ?array
    {
        $assignment = DeliveryAssignment::forPartnerAndOrder($partnerId, $orderId);
        if ($assignment === null) {
            return null;
        }

        $order = Order::find($orderId);
        if ($order === null) {
            return null;
        }

        return [
            'assignment' => $assignment,
            'order' => $order,
            'items' => Order::items($orderId),
        ];
    }

    /**
     * Advances an assignment through its lifecycle and mirrors the change
     * onto `orders`/`shipments` (so the admin panel's existing order and
     * shipment views need no changes to show what the rider is doing) and
     * `order_status_history` (so it lands in the same audit trail admin
     * actions use).
     *
     * @throws \RuntimeException if the order isn't assigned to this rider or the status is invalid
     */
    public function updateStatus(
        int $partnerId,
        int $orderId,
        string $status,
        ?string $note,
        ?float $lat,
        ?float $lng,
    ): array {
        if (!in_array($status, DeliveryAssignment::STATUSES, true)) {
            throw new \RuntimeException('Invalid status.');
        }

        $assignment = DeliveryAssignment::forPartnerAndOrder($partnerId, $orderId);
        if ($assignment === null) {
            throw new \RuntimeException('This order is not assigned to you.');
        }

        $db = Database::instance();

        $db->transaction(function (Database $db) use ($assignment, $orderId, $partnerId, $status, $note) {
            $timestampColumn = match ($status) {
                'picked_up' => 'picked_up_at',
                'out_for_delivery' => 'out_for_delivery_at',
                'delivered' => 'delivered_at',
                default => null,
            };

            $update = ['status' => $status, 'updated_at' => now()];
            if ($timestampColumn !== null) {
                $update[$timestampColumn] = now();
            }
            if ($note !== null && $note !== '') {
                $update['notes'] = $note;
            }
            $db->update('delivery_assignments', $update, 'id = :id', ['id' => $assignment['id']]);

            $this->mirrorOntoOrderAndShipment($orderId, $status, $partnerId, $note);
        });

        return DeliveryAssignment::find((int) $assignment['id']) ?? $assignment;
    }

    private function mirrorOntoOrderAndShipment(int $orderId, string $deliveryStatus, int $partnerId, ?string $note): void
    {
        $db = Database::instance();

        [$orderStatus, $shipmentStatus, $historyNote] = match ($deliveryStatus) {
            'picked_up' => ['processing', 'packed', 'Picked up by delivery partner' . ($note !== null && $note !== '' ? " — {$note}" : '')],
            'out_for_delivery' => ['shipped', 'shipped', 'Out for delivery' . ($note !== null && $note !== '' ? " — {$note}" : '')],
            'delivered' => ['delivered', 'delivered', 'Delivered' . ($note !== null && $note !== '' ? " — {$note}" : '')],
            'failed' => [null, null, 'Delivery attempt failed' . ($note !== null && $note !== '' ? " — {$note}" : '')],
            default => [null, null, null],
        };

        if ($orderStatus !== null) {
            Order::updateWhere($orderId, ['status' => $orderStatus]);
        }

        if ($historyNote !== null) {
            Order::addStatusHistory($orderId, $orderStatus ?? (string) Order::find($orderId)['status'], $historyNote, $partnerId);
        }

        if ($shipmentStatus !== null) {
            $existing = $db->selectOne('SELECT * FROM shipments WHERE order_id = :id ORDER BY id DESC LIMIT 1', ['id' => $orderId]);
            $data = [
                'status' => $shipmentStatus,
                'shipped_at' => $shipmentStatus === 'shipped' ? now() : ($existing['shipped_at'] ?? null),
                'delivered_at' => $shipmentStatus === 'delivered' ? now() : ($existing['delivered_at'] ?? null),
                'updated_at' => now(),
            ];

            if ($existing !== null) {
                $db->update('shipments', $data, 'id = :id', ['id' => $existing['id']]);
            } else {
                $data['order_id'] = $orderId;
                $data['carrier'] = null;
                $data['tracking_number'] = null;
                $data['created_at'] = now();
                $db->insert('shipments', $data);
            }
        }
    }

    /**
     * A cheap location ping, throttled by the token's own rate limit rather
     * than anything here. `$orderId`, if given, must be a currently-open
     * assignment for this rider — silently ignored (not stored) otherwise,
     * so a stray/late ping from a finished delivery can't attach itself to
     * the wrong order.
     */
    public function recordLocation(int $partnerId, ?int $orderId, float $lat, float $lng): void
    {
        if ($orderId !== null) {
            $assignment = DeliveryAssignment::forPartnerAndOrder($partnerId, $orderId);
            if ($assignment === null || !in_array($assignment['status'], DeliveryAssignment::OPEN_STATUSES, true)) {
                $orderId = null;
            }
        }

        Database::instance()->insert('delivery_locations', [
            'delivery_partner_id' => $partnerId,
            'order_id' => $orderId,
            'lat' => $lat,
            'lng' => $lng,
            'recorded_at' => now(),
            'created_at' => now(),
        ]);
    }

    /** @return array<string, mixed>|null the rider's most recent ping for this order, if any */
    public static function latestLocationForOrder(int $orderId): ?array
    {
        return Database::instance()->selectOne(
            'SELECT lat, lng, recorded_at FROM delivery_locations WHERE order_id = :id ORDER BY id DESC LIMIT 1',
            ['id' => $orderId],
        );
    }

    /** @return array<int, array<string, mixed>> every active rider, for the admin assignment dropdown */
    public static function availablePartners(): array
    {
        return User::withRole('delivery');
    }

    public static function assign(int $orderId, int $partnerId, ?int $assignedByUserId, ?string $notes = null): int
    {
        $db = Database::instance();

        $id = $db->insert('delivery_assignments', [
            'order_id' => $orderId,
            'delivery_partner_id' => $partnerId,
            'status' => 'assigned',
            'assigned_by_user_id' => $assignedByUserId,
            'notes' => $notes !== null && $notes !== '' ? $notes : null,
            'assigned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $partner = User::find($partnerId);
        Order::addStatusHistory(
            $orderId,
            (string) Order::find($orderId)['status'],
            'Assigned to delivery partner: ' . ($partner['name'] ?? "#{$partnerId}"),
            $assignedByUserId,
        );

        return $id;
    }
}
