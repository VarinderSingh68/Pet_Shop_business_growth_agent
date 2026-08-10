<?php

declare(strict_types=1);

use App\Core\Database;

$db = Database::instance();

// --- Reviews ------------------------------------------------------------

$existingReviews = (int) ($db->selectOne("SELECT COUNT(*) c FROM reviews")['c'] ?? 0);

if ($existingReviews < 15) {
    $reviewTexts = [
        ['title' => 'My dog loves this', 'body' => 'Switched from another brand and the difference in his coat within a month was noticeable. No more itching either.', 'rating' => 5],
        ['title' => 'Good value', 'body' => "Does what it says. Wouldn't call it exciting but my cat eats it every time, which is the real test.", 'rating' => 4],
        ['title' => 'Arrived quickly', 'body' => 'Ordered on a Tuesday, had it by Thursday. Packaging was solid, nothing damaged.', 'rating' => 5],
        ['title' => 'Decent but pricey', 'body' => "Works well but I've seen it cheaper elsewhere. Still, the quality justifies most of the gap.", 'rating' => 3],
        ['title' => 'Highly recommend', 'body' => 'Been using this for six months now as part of a subscription. Never runs out, never think about it.', 'rating' => 5],
        ['title' => 'Picky eater approved', 'body' => "My cat rejects almost everything new but went straight for this. That's rare enough to write a review about.", 'rating' => 5],
        ['title' => 'Good but strong smell', 'body' => 'Product works as described. The smell is stronger than I expected but my dog doesn\'t seem to mind.', 'rating' => 4],
        ['title' => "Exactly as described", 'body' => 'No surprises, which is exactly what I want from a reorder. Consistent quality every time.', 'rating' => 5],
        ['title' => 'Solid everyday choice', 'body' => "Not fancy, but reliable. I've been buying this for over a year for my two dogs.", 'rating' => 4],
        ['title' => 'Great for sensitive stomachs', 'body' => 'My dog has a sensitive stomach and this is one of the few foods that hasn\'t caused any issues.', 'rating' => 5],
        ['title' => 'Would buy again', 'body' => "Simple, does the job, fair price. That's really all I need from this kind of product.", 'rating' => 4],
        ['title' => 'Perfect for kittens', 'body' => 'My kitten took to this immediately. Vet approved of the ingredient list too.', 'rating' => 5],
        ['title' => 'Fine, nothing special', 'body' => "It's fine. Does what it needs to. Wouldn't go out of my way to recommend it but wouldn't avoid it either.", 'rating' => 3],
        ['title' => 'Great customer service too', 'body' => 'Product is good and when I had a question about sizing, support replied within a day.', 'rating' => 5],
        ['title' => 'Noticeable improvement', 'body' => "Within two weeks I noticed my dog scratching less. Can't say for certain it's the food but the timing lines up.", 'rating' => 4],
        ['title' => 'Convenient subscription', 'body' => 'Set up a subscription so I never think about reordering. Shows up right before we run out every time.', 'rating' => 5],
    ];

    $productIds = array_column($db->select('SELECT id FROM products'), 'id');
    $demoCustomers = $db->select("SELECT id FROM users WHERE email LIKE 'demo.%@example.com' OR email = 'testcustomer@example.com'");

    $created = 0;
    foreach ($reviewTexts as $i => $review) {
        if ($productIds === [] || $demoCustomers === []) {
            break;
        }

        $productId = $productIds[array_rand($productIds)];
        $customer = $demoCustomers[array_rand($demoCustomers)];

        $verified = $db->selectOne(
            "SELECT 1 AS x FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE o.user_id = :uid AND oi.product_id = :pid AND o.status = 'delivered' LIMIT 1",
            ['uid' => $customer['id'], 'pid' => $productId],
        ) !== null;

        $db->insert('reviews', [
            'product_id' => $productId,
            'user_id' => $customer['id'],
            'rating' => $review['rating'],
            'title' => $review['title'],
            'body' => $review['body'],
            'is_verified_purchase' => $verified ? 1 : 0,
            'status' => $i < 2 ? 'pending' : 'approved', // leave a couple pending to show the moderation queue
            'created_at' => (new DateTimeImmutable('-' . random_int(2, 180) . ' days'))->format('Y-m-d H:i:s'),
            'updated_at' => now(),
        ]);
        $created++;
    }

    // Recalculate each affected product's avg_rating/review_count.
    foreach ($db->select("SELECT DISTINCT product_id FROM reviews WHERE status = 'approved'") as $row) {
        \App\Models\Product::recalculateRating((int) $row['product_id']);
    }

    echo "  Reviews: {$created} created.\n";
} else {
    echo "  Reviews already seeded ({$existingReviews}) — skipped.\n";
}

// --- Appointments ---------------------------------------------------------

$existingAppointments = (int) ($db->selectOne("SELECT COUNT(*) c FROM appointments WHERE booking_number LIKE 'APT-DEMO-%'")['c'] ?? 0);

if ($existingAppointments === 0) {
    $demoCustomersWithPets = $db->select(
        "SELECT u.id AS user_id, MIN(p.id) AS pet_id FROM users u
         JOIN pets p ON p.user_id = u.id
         WHERE u.email LIKE 'demo.%@example.com'
         GROUP BY u.id",
    );

    $slots = $db->select(
        "SELECT id, service_id, staff_id, start_at FROM service_slots WHERE is_booked = 0 ORDER BY start_at ASC LIMIT 25",
    );

    $created = 0;
    foreach (array_slice($slots, 0, 20) as $i => $slot) {
        if ($demoCustomersWithPets === []) {
            break;
        }

        $customer = $demoCustomersWithPets[$i % count($demoCustomersWithPets)];
        $isPast = strtotime((string) $slot['start_at']) < time();
        $status = $isPast ? (random_int(1, 20) === 1 ? 'no_show' : 'completed') : 'booked';

        $db->transaction(function (Database $db) use ($slot, $customer, $status, &$created) {
            $bookingNumber = 'APT-DEMO-' . strtoupper(bin2hex(random_bytes(4)));

            $db->insert('appointments', [
                'booking_number' => $bookingNumber,
                'service_id' => $slot['service_id'],
                'staff_id' => $slot['staff_id'],
                'slot_id' => $slot['id'],
                'user_id' => $customer['user_id'],
                'pet_id' => $customer['pet_id'],
                'status' => $status,
                'payment_status' => 'not_required',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $db->update('service_slots', ['is_booked' => 1], 'id = :id', ['id' => $slot['id']]);
            $created++;
        });
    }

    echo "  Appointments: {$created} booked across demo customers.\n";
} else {
    echo "  Demo appointments already seeded ({$existingAppointments}) — skipped.\n";
}
