<?php

declare(strict_types=1);

use App\Core\Database;

$db = Database::instance();

$coupons = [
    [
        'code' => 'WELCOME10', 'description' => '10% off your first order', 'type' => 'percent', 'value' => 10,
        'min_order_paise' => null, 'max_discount_paise' => 30000, 'usage_limit' => null, 'usage_limit_per_customer' => 1,
    ],
    [
        'code' => 'FREESHIP', 'description' => 'Free shipping on orders over ₹500', 'type' => 'fixed', 'value' => 7900,
        'min_order_paise' => 50000, 'max_discount_paise' => null, 'usage_limit' => null, 'usage_limit_per_customer' => null,
    ],
    [
        'code' => 'PAWFECT200', 'description' => '₹200 off orders over ₹1500', 'type' => 'fixed', 'value' => 20000,
        'min_order_paise' => 150000, 'max_discount_paise' => null, 'usage_limit' => 500, 'usage_limit_per_customer' => 2,
    ],
    [
        'code' => 'PUPPY15', 'description' => '15% off for new puppy and kitten owners', 'type' => 'percent', 'value' => 15,
        'min_order_paise' => null, 'max_discount_paise' => 50000, 'usage_limit' => null, 'usage_limit_per_customer' => 1,
    ],
    [
        'code' => 'VIP500', 'description' => '₹500 off orders over ₹3000 — loyal customer appreciation', 'type' => 'fixed', 'value' => 50000,
        'min_order_paise' => 300000, 'max_discount_paise' => null, 'usage_limit' => 200, 'usage_limit_per_customer' => 1,
    ],
];

$count = 0;
foreach ($coupons as $c) {
    $existing = $db->selectOne('SELECT id FROM coupons WHERE code = :code', ['code' => $c['code']]);
    if ($existing !== null) {
        continue;
    }

    $db->insert('coupons', [
        'code' => $c['code'],
        'description' => $c['description'],
        'type' => $c['type'],
        'value' => $c['value'],
        'min_order_paise' => $c['min_order_paise'],
        'max_discount_paise' => $c['max_discount_paise'],
        'usage_limit' => $c['usage_limit'],
        'usage_limit_per_customer' => $c['usage_limit_per_customer'],
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $count++;
}

echo "  Coupons: {$count} created.\n";
