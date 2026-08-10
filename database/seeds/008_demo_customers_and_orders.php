<?php

declare(strict_types=1);

use App\Core\Database;

$db = Database::instance();

$existingDemoCustomers = (int) ($db->selectOne(
    "SELECT COUNT(*) c FROM users WHERE email LIKE 'demo.%@example.com'",
)['c'] ?? 0);

if ($existingDemoCustomers > 0) {
    echo "  Demo customers already seeded ({$existingDemoCustomers}) — skipped.\n";
    return;
}

$customerRoleId = (int) $db->selectOne("SELECT id FROM roles WHERE slug = 'customer'")['id'];

$firstNames = ['Aarav', 'Vivaan', 'Aditya', 'Vihaan', 'Arjun', 'Sai', 'Reyansh', 'Krishna', 'Ishaan', 'Rohan',
    'Ananya', 'Diya', 'Saanvi', 'Aadhya', 'Kiara', 'Myra', 'Anika', 'Riya', 'Priya', 'Fatima',
    'Rahul', 'Amit', 'Vikram', 'Sanjay', 'Karan', 'Nikhil', 'Arun', 'Deepak', 'Manish', 'Suresh',
    'Neha', 'Pooja', 'Kavya', 'Shreya', 'Divya', 'Meera', 'Lakshmi', 'Anjali', 'Sneha', 'Ritu'];
$lastNames = ['Sharma', 'Verma', 'Gupta', 'Reddy', 'Rao', 'Nair', 'Iyer', 'Menon', 'Pillai', 'Kumar',
    'Singh', 'Patel', 'Shah', 'Mehta', 'Joshi', 'Desai', 'Kulkarni', 'Bhat', 'Chatterjee', 'Banerjee'];

$petNamesDog = ['Bruno', 'Rocky', 'Charlie', 'Max', 'Leo', 'Simba', 'Coco', 'Tommy', 'Bailey', 'Jack'];
$petNamesCat = ['Whiskers', 'Oreo', 'Milo', 'Luna', 'Bella', 'Simba', 'Tom', 'Shadow', 'Misty', 'Salem'];
$dogBreeds = ['Labrador', 'Golden Retriever', 'Indian Pariah', 'German Shepherd', 'Beagle', 'Pug', 'Shih Tzu', 'Cocker Spaniel'];
$catBreeds = ['Persian', 'Indian Shorthair', 'Siamese', 'Maine Coon', 'British Shorthair'];

/** @return array<int, array{id: int, weight_grams: ?int, price_paise: int, feeding_grams_per_day: ?int}> */
function seed_food_variants(Database $db): array
{
    return $db->select(
        "SELECT v.id, v.weight_grams, v.price_paise, p.feeding_grams_per_day
         FROM product_variants v JOIN products p ON p.id = v.product_id
         WHERE p.feeding_grams_per_day IS NOT NULL AND v.weight_grams IS NOT NULL AND v.deleted_at IS NULL",
    );
}

/** @return array<int, array{id: int, price_paise: int}> */
function seed_any_variants(Database $db): array
{
    return $db->select('SELECT id, price_paise FROM product_variants WHERE deleted_at IS NULL');
}

$foodVariants = seed_food_variants($db);
$anyVariants = seed_any_variants($db);
$taxRate = 0.05;

$cities = [
    ['city' => 'Bengaluru', 'state' => 'Karnataka', 'postal' => '560001'],
    ['city' => 'Mumbai', 'state' => 'Maharashtra', 'postal' => '400001'],
    ['city' => 'Pune', 'state' => 'Maharashtra', 'postal' => '411001'],
    ['city' => 'Chennai', 'state' => 'Tamil Nadu', 'postal' => '600001'],
    ['city' => 'Hyderabad', 'state' => 'Telangana', 'postal' => '500001'],
];

// Engagement profiles determine each customer's order count and recency —
// this is what gives the RFM scorer and segments something real to work with.
$profiles = [
    'loyal' => ['count' => 8, 'orders' => [5, 8], 'recencyDays' => [1, 20]],
    'occasional' => ['count' => 12, 'orders' => [2, 4], 'recencyDays' => [10, 70]],
    'new' => ['count' => 8, 'orders' => [1, 1], 'recencyDays' => [1, 25]],
    'lapsed' => ['count' => 7, 'orders' => [2, 4], 'recencyDays' => [95, 200]],
    'browsers' => ['count' => 5, 'orders' => [0, 0], 'recencyDays' => [0, 0]],
];
// 40 customers total, ~115 orders — matches the spec's ~40 customers / ~120 orders target.

$customerIndex = 0;
$totalOrders = 0;
$totalPets = 0;
$totalCustomers = 0;

foreach ($profiles as $profileName => $profile) {
    for ($p = 0; $p < $profile['count']; $p++) {
        $first = $firstNames[$customerIndex % count($firstNames)];
        $last = $lastNames[($customerIndex * 7) % count($lastNames)];
        $email = 'demo.' . strtolower($first) . '.' . strtolower($last) . $customerIndex . '@example.com';
        $phone = '9' . str_pad((string) (100000000 + $customerIndex * 137), 9, '0', STR_PAD_LEFT);
        $location = $cities[$customerIndex % count($cities)];

        // Order timing is computed first so the account's created_at can be
        // pinned to predate every order it's about to receive — otherwise a
        // "lapsed" customer's oldest order could land before their signup date.
        [$minOrders, $maxOrders] = $profile['orders'];
        $orderCount = random_int($minOrders, $maxOrders);
        [$minRecency, $maxRecency] = $profile['recencyDays'];
        $lastOrderDaysAgo = $orderCount > 0 ? random_int($minRecency, max($minRecency, $maxRecency)) : null;
        $oldestOrderDaysAgo = $orderCount > 0 ? $lastOrderDaysAgo + (($orderCount - 1) * 35) : 0;
        $createdDaysAgo = max(random_int(30, 400), $oldestOrderDaysAgo + random_int(5, 30));

        $userId = $db->insert('users', [
            'role_id' => $customerRoleId,
            'name' => "{$first} {$last}",
            'email' => $email,
            'phone' => $phone,
            'password_hash' => password_hash('DemoPass123!', PASSWORD_ARGON2ID),
            'is_active' => 1,
            'email_verified_at' => now(),
            'created_at' => (new DateTimeImmutable("-{$createdDaysAgo} days"))->format('Y-m-d H:i:s'),
            'updated_at' => now(),
        ]);
        $totalCustomers++;

        // Address
        $db->insert('addresses', [
            'user_id' => $userId,
            'label' => 'Home',
            'full_name' => "{$first} {$last}",
            'phone' => $phone,
            'line1' => (10 + $customerIndex) . ' ' . ['MG Road', 'Park Street', 'Church Street', 'Brigade Road', 'Linking Road'][$customerIndex % 5],
            'city' => $location['city'],
            'state' => $location['state'],
            'postal_code' => $location['postal'],
            'country' => 'IN',
            'is_default' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1-2 pets per customer, varied ages to populate puppy/senior segments
        $petCount = random_int(1, 2);
        for ($pt = 0; $pt < $petCount; $pt++) {
            $species = random_int(0, 1) === 0 ? 'dog' : 'cat';
            $ageRoll = random_int(1, 10);
            $ageDays = match (true) {
                $ageRoll <= 2 => random_int(60, 350),      // puppy/kitten
                $ageRoll <= 8 => random_int(400, 2200),     // adult
                default => random_int(2600, 4000),          // senior
            };

            $db->insert('pets', [
                'user_id' => $userId,
                'name' => $species === 'dog' ? $petNamesDog[array_rand($petNamesDog)] : $petNamesCat[array_rand($petNamesCat)],
                'species' => $species,
                'breed' => $species === 'dog' ? $dogBreeds[array_rand($dogBreeds)] : $catBreeds[array_rand($catBreeds)],
                'birthday' => (new DateTimeImmutable("-{$ageDays} days"))->format('Y-m-d'),
                'weight_grams' => $species === 'dog' ? random_int(3000, 35000) : random_int(2000, 6000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $totalPets++;
        }

        // Orders per the engagement profile (orderCount/lastOrderDaysAgo computed above)
        for ($o = 0; $o < $orderCount; $o++) {
            // Space orders backward in time from the most recent one.
            $daysAgo = $lastOrderDaysAgo + ($o * random_int(15, 35));
            $placedAt = (new DateTimeImmutable("-{$daysAgo} days"))->format('Y-m-d H:i:s');

            $itemCount = random_int(1, 3);
            $lineItems = [];
            $subtotal = 0;

            // Bias toward food products so replenishment/subscription-candidate logic has real signal.
            $pool = (random_int(1, 100) <= 70 && $foodVariants !== []) ? $foodVariants : $anyVariants;

            for ($li = 0; $li < $itemCount; $li++) {
                $variant = $pool[array_rand($pool)];
                $qty = random_int(1, 2);
                $lineTotal = (int) $variant['price_paise'] * $qty;
                $lineItems[] = ['variant_id' => $variant['id'], 'qty' => $qty, 'unit' => (int) $variant['price_paise'], 'line_total' => $lineTotal];
                $subtotal += $lineTotal;
            }

            $shipping = $subtotal >= 99900 ? 0 : 7900;
            $tax = (int) round($subtotal * $taxRate);
            $total = $subtotal + $shipping + $tax;

            $isRecent = $daysAgo < 3;
            $status = match (true) {
                $isRecent && random_int(0, 1) === 0 => 'confirmed',
                $isRecent => 'processing',
                random_int(1, 100) <= 4 => 'cancelled',
                default => 'delivered',
            };

            $orderId = $db->insert('orders', [
                'order_number' => 'HT-DEMO-' . strtoupper(bin2hex(random_bytes(4))),
                'user_id' => $userId,
                'status' => $status,
                'currency' => 'INR',
                'subtotal_paise' => $subtotal,
                'discount_paise' => 0,
                'shipping_paise' => $shipping,
                'tax_paise' => $tax,
                'total_paise' => $total,
                'payment_method' => random_int(0, 1) === 0 ? 'cod' : 'razorpay',
                'payment_status' => $status === 'cancelled' ? 'failed' : 'paid',
                'shipping_full_name' => "{$first} {$last}",
                'shipping_phone' => $phone,
                'shipping_line1' => (10 + $customerIndex) . ' ' . ['MG Road', 'Park Street', 'Church Street'][$customerIndex % 3],
                'shipping_city' => $location['city'],
                'shipping_state' => $location['state'],
                'shipping_postal_code' => $location['postal'],
                'shipping_country' => 'IN',
                'placed_at' => $placedAt,
                'created_at' => $placedAt,
                'updated_at' => $placedAt,
            ]);

            foreach ($lineItems as $item) {
                $productRow = $db->selectOne(
                    'SELECT p.name, v.label, v.sku FROM product_variants v JOIN products p ON p.id = v.product_id WHERE v.id = :id',
                    ['id' => $item['variant_id']],
                );
                $db->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $db->selectOne('SELECT product_id FROM product_variants WHERE id = :id', ['id' => $item['variant_id']])['product_id'],
                    'variant_id' => $item['variant_id'],
                    'product_name_snapshot' => $productRow['name'],
                    'variant_label_snapshot' => $productRow['label'],
                    'sku_snapshot' => $productRow['sku'],
                    'unit_price_paise' => $item['unit'],
                    'quantity' => $item['qty'],
                    'line_total_paise' => $item['line_total'],
                    'created_at' => $placedAt,
                ]);
            }

            $db->insert('order_status_history', [
                'order_id' => $orderId,
                'status' => $status,
                'note' => 'Seeded demo order',
                'created_at' => $placedAt,
            ]);

            $totalOrders++;
        }

        $customerIndex++;
    }
}

echo "  Demo customers: {$totalCustomers} created, {$totalPets} pets, {$totalOrders} orders spread across engagement profiles.\n";
