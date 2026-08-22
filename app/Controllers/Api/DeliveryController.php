<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use App\Services\DeliveryService;

/**
 * Backs the delivery-partner (rider) Android app. Everything here except
 * login() and track() requires a delivery-role bearer token — see
 * DeliveryTokenMiddleware, registered in routes/api.php as 'delivery_token'.
 */
final class DeliveryController extends Controller
{
    public function __construct(
        private readonly DeliveryService $delivery = new DeliveryService(),
    ) {
    }

    /**
     * Stateless login for the mobile app: exchanges email+password for a
     * personal bearer token (stored the same way an admin-issued API token
     * is). No session/cookie is created — the app holds the token itself.
     */
    public function login(Request $request): void
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $deviceName = $request->input('device_name');

        if ($email === '' || $password === '') {
            $this->json(['message' => 'Email and password are required.'], 422);
        }

        $result = $this->delivery->login($email, $password, is_string($deviceName) ? $deviceName : null);

        if ($result === null) {
            $this->json(['message' => 'Invalid credentials, or this account is not a delivery partner.'], 401);
        }

        $partner = $result['partner'];
        $this->json([
            'token' => $result['token'],
            'partner' => ['id' => (int) $partner['id'], 'name' => $partner['name'], 'email' => $partner['email']],
        ]);
    }

    public function register(Request $request): void
    {
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $deviceName = $request->input('device_name');

        if ($name === '' || $email === '' || $password === '') {
            $this->json(['message' => 'Name, email and password are required.'], 422);
        }

        try {
            $result = $this->delivery->register($name, $email, $password, is_string($deviceName) ? $deviceName : null);
            if ($result === null) {
                $this->json(['message' => 'Registration failed.'], 500);
            }

            $partner = $result['partner'];
            $this->json([
                'token' => $result['token'],
                'partner' => ['id' => (int) $partner['id'], 'name' => $partner['name'], 'email' => $partner['email']],
            ]);
        } catch (\RuntimeException $e) {
            $this->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Assigned orders for the authenticated rider. Defaults to only the
     * still-open ones (assigned/picked_up/out_for_delivery) so the app's
     * home screen isn't cluttered with finished deliveries; pass
     * ?status=delivered (or any single status, or "all") to see others.
     */
    public function orders(Request $request): void
    {
        $partnerId = (int) $request->getAttribute('delivery_partner_id');
        $status = (string) $request->query('status', '');

        if ($status === '') {
            $rows = array_merge(
                DeliveryAssignment::forPartner($partnerId, 'assigned'),
                DeliveryAssignment::forPartner($partnerId, 'picked_up'),
                DeliveryAssignment::forPartner($partnerId, 'out_for_delivery'),
            );
            usort($rows, static fn (array $a, array $b) => strcmp((string) $b['assigned_at'], (string) $a['assigned_at']));
        } else {
            $rows = $this->delivery->assignedOrders($partnerId, $status === 'all' ? null : $status);
        }

        $this->json(['data' => array_map([self::class, 'formatAssignment'], $rows)]);
    }

    public function show(Request $request, string $id): void
    {
        $partnerId = (int) $request->getAttribute('delivery_partner_id');
        $found = $this->delivery->orderForPartner($partnerId, (int) $id);

        if ($found === null) {
            $this->json(['message' => 'Order not found or not assigned to you.'], 404);
        }

        $order = $found['order'];
        $this->json([
            'data' => [
                ...self::formatAssignment($found['assignment'] + self::orderColumnsForAssignment($order)),
                'items' => array_map(static fn (array $item) => [
                    'product_name' => $item['product_name_snapshot'],
                    'variant_label' => $item['variant_label_snapshot'],
                    'quantity' => (int) $item['quantity'],
                    'line_total_paise' => (int) $item['line_total_paise'],
                ], $found['items']),
            ],
        ]);
    }

    /**
     * Advances the delivery: 'picked_up' -> 'out_for_delivery' ->
     * 'delivered' (or 'failed' at any point). Mirrors onto the order and
     * shipment records so the admin panel reflects it with no changes
     * there. An optional lat/lng is recorded as a location ping tagged to
     * this order (e.g. "delivered at").
     */
    public function updateStatus(Request $request, string $id): void
    {
        $partnerId = (int) $request->getAttribute('delivery_partner_id');
        $status = (string) $request->input('status', '');
        $note = $request->input('note');
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        try {
            $assignment = $this->delivery->updateStatus(
                $partnerId,
                (int) $id,
                $status,
                is_string($note) ? $note : null,
                $lat !== null ? (float) $lat : null,
                $lng !== null ? (float) $lng : null,
            );
        } catch (\RuntimeException $e) {
            $this->json(['message' => $e->getMessage()], 422);
        }

        if ($lat !== null && $lng !== null) {
            $this->delivery->recordLocation($partnerId, (int) $id, (float) $lat, (float) $lng);
        }

        $this->json(['data' => self::formatAssignment($assignment)]);
    }

    /**
     * A cheap, frequent location ping while a delivery is active. `order_id`
     * is optional (a rider between deliveries can still ping); when given,
     * it's only stored against the order if that assignment is still open
     * for this rider (see DeliveryService::recordLocation).
     */
    public function location(Request $request): void
    {
        $partnerId = (int) $request->getAttribute('delivery_partner_id');
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $orderId = $request->input('order_id');

        if ($lat === null || $lng === null) {
            $this->json(['message' => 'lat and lng are required.'], 422);
        }

        $this->delivery->recordLocation($partnerId, $orderId !== null ? (int) $orderId : null, (float) $lat, (float) $lng);

        $this->json(['status' => 'ok']);
    }

    /**
     * Public, signed order tracking — the "where's my delivery" view a
     * customer-facing page can poll every 10-15s. Not authenticated with a
     * bearer token (the customer isn't a rider); instead a time-limited
     * HMAC signature, same pattern as InvoiceService's guest invoice links.
     */
    public function track(Request $request, string $orderNumber): void
    {
        $expires = (int) $request->query('expires', 0);
        $signature = (string) $request->query('sig', '');

        $order = Order::findByNumber($orderNumber);
        if ($order === null || !self::verifyTrackingSignature((int) $order['id'], $expires, $signature)) {
            $this->json(['message' => 'Not found or link expired.'], 404);
        }

        $assignment = DeliveryAssignment::currentForOrder((int) $order['id']);
        $ping = DeliveryService::latestLocationForOrder((int) $order['id']);

        $this->json([
            'order_number' => $order['order_number'],
            'status' => $order['status'],
            'delivery_status' => $assignment['status'] ?? null,
            'location' => $ping !== null ? ['lat' => (float) $ping['lat'], 'lng' => (float) $ping['lng'], 'recorded_at' => $ping['recorded_at']] : null,
        ]);
    }

    /** Builds the signed URL a storefront tracking page can poll — call from wherever that page is rendered. */
    public static function signedTrackingUrl(array $order, int $ttlSeconds = 60 * 60 * 6): string
    {
        $expires = time() + $ttlSeconds;
        $sig = self::signTracking((int) $order['id'], $expires);
        return url('api/v1/delivery/track/' . $order['order_number'] . "?expires={$expires}&sig={$sig}");
    }

    private static function signTracking(int $orderId, int $expires): string
    {
        return hash_hmac('sha256', 'delivery-track|' . $orderId . '|' . $expires, (string) config('app.key'));
    }

    private static function verifyTrackingSignature(int $orderId, int $expires, string $signature): bool
    {
        if ($expires < time() || $signature === '') {
            return false;
        }
        return hash_equals(self::signTracking($orderId, $expires), $signature);
    }

    /** @param array<string, mixed> $row an assignment row, optionally joined with order columns */
    private static function formatAssignment(array $row): array
    {
        return [
            'assignment_id' => (int) $row['id'],
            'order_id' => (int) $row['order_id'],
            'order_number' => $row['order_number'] ?? null,
            'status' => $row['status'],
            'order_status' => $row['order_status'] ?? null,
            'assigned_at' => $row['assigned_at'],
            'picked_up_at' => $row['picked_up_at'] ?? null,
            'out_for_delivery_at' => $row['out_for_delivery_at'] ?? null,
            'delivered_at' => $row['delivered_at'] ?? null,
            'notes' => $row['notes'] ?? null,
            'total_paise' => isset($row['total_paise']) ? (int) $row['total_paise'] : null,
            'currency' => $row['currency'] ?? null,
            'payment_method' => $row['payment_method'] ?? null,
            'payment_status' => $row['payment_status'] ?? null,
            'customer_notes' => $row['customer_notes'] ?? null,
            'shipping_address' => [
                'name' => $row['shipping_full_name'] ?? null,
                'phone' => $row['shipping_phone'] ?? null,
                'line1' => $row['shipping_line1'] ?? null,
                'line2' => $row['shipping_line2'] ?? null,
                'city' => $row['shipping_city'] ?? null,
                'state' => $row['shipping_state'] ?? null,
                'postal_code' => $row['shipping_postal_code'] ?? null,
                'country' => $row['shipping_country'] ?? null,
            ],
        ];
    }

    /** Reshapes an `orders` row's columns to the keys formatAssignment() expects when it wasn't already joined. */
    private static function orderColumnsForAssignment(array $order): array
    {
        return [
            'order_number' => $order['order_number'],
            'order_status' => $order['status'],
            'total_paise' => $order['total_paise'],
            'currency' => $order['currency'],
            'payment_method' => $order['payment_method'],
            'payment_status' => $order['payment_status'],
            'customer_notes' => $order['customer_notes'],
            'shipping_full_name' => $order['shipping_full_name'],
            'shipping_phone' => $order['shipping_phone'],
            'shipping_line1' => $order['shipping_line1'],
            'shipping_line2' => $order['shipping_line2'],
            'shipping_city' => $order['shipping_city'],
            'shipping_state' => $order['shipping_state'],
            'shipping_postal_code' => $order['shipping_postal_code'],
            'shipping_country' => $order['shipping_country'],
        ];
    }
}
