<?php

declare(strict_types=1);

namespace App\Services\Growth;

use App\Core\Database;
use App\Core\Mailer;
use App\Models\GrowthAction;
use App\Models\Notification;

final class AutomationService
{
    /**
     * Three-step abandoned-cart sequence (1h / 24h / 72h), logged-in
     * customers only (guests have no email until checkout). A cart's items
     * are deleted the moment an order is placed, so it simply stops
     * appearing in this query — no separate "did they check out" check needed.
     */
    public function processAbandonedCarts(): string
    {
        $db = Database::instance();

        $carts = $db->select(
            "SELECT c.id AS cart_id, c.user_id, u.name, u.email, MAX(ci.updated_at) AS last_activity
             FROM carts c
             JOIN cart_items ci ON ci.cart_id = c.id
             JOIN users u ON u.id = c.user_id
             WHERE c.user_id IS NOT NULL
             GROUP BY c.id, c.user_id, u.name, u.email",
        );

        $steps = [
            1 => ['minutes' => 60, 'type' => 'abandoned_cart_1', 'subject' => 'Forget something?'],
            2 => ['minutes' => 1440, 'type' => 'abandoned_cart_2', 'subject' => 'Still thinking it over?'],
            3 => ['minutes' => 4320, 'type' => 'abandoned_cart_3', 'subject' => "Here's 10% off to help you decide"],
        ];

        $sent = 0;

        foreach ($carts as $cart) {
            $minutesSince = (int) floor((time() - strtotime((string) $cart['last_activity'])) / 60);

            foreach ($steps as $step => $config) {
                if ($minutesSince < $config['minutes']) {
                    continue;
                }
                if (Notification::alreadySentFor((int) $cart['user_id'], $config['type'], 'cart', (int) $cart['cart_id'])) {
                    continue;
                }

                $items = $db->select(
                    'SELECT p.name FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = :cid',
                    ['cid' => $cart['cart_id']],
                );
                $itemList = implode(', ', array_column($items, 'name'));

                $body = "Hi {$cart['name']},\n\nYou left {$itemList} in your cart.";
                $couponCode = null;

                if ($step === 3) {
                    $couponCode = $this->generateRecoveryCoupon();
                    $body .= "\n\nUse code {$couponCode} for 10% off if you check out in the next 3 days.";
                }

                $body .= "\n\nFinish your order: " . url('/cart');

                $this->notify((int) $cart['user_id'], $config['type'], 'email', $config['subject'], $body, 'cart', (int) $cart['cart_id']);
                $sent++;
            }
        }

        if ($sent > 0) {
            GrowthAction::log('abandoned_cart', "Sent {$sent} abandoned-cart reminder(s) across the 1h/24h/72h sequence.", ['affected_count' => $sent]);
        }

        return "{$sent} reminder(s) sent";
    }

    /**
     * Predicts the run-out date from the variant's weight and the product's
     * average daily feeding rate (feeding_grams_per_day), and prompts a
     * reorder 2 days before — only for each customer's most recent purchase
     * of that product, so an old order doesn't keep re-triggering reminders.
     */
    public function processReplenishment(): string
    {
        $db = Database::instance();

        $candidates = $db->select(
            "SELECT o.id AS order_id, o.user_id, o.placed_at, oi.product_id, oi.variant_id,
                    p.name AS product_name, p.feeding_grams_per_day, v.weight_grams, v.label AS variant_label,
                    u.name AS user_name, u.email
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             JOIN products p ON p.id = oi.product_id
             JOIN product_variants v ON v.id = oi.variant_id
             JOIN users u ON u.id = o.user_id
             WHERE o.status NOT IN ('cancelled')
               AND o.user_id IS NOT NULL
               AND p.feeding_grams_per_day IS NOT NULL
               AND v.weight_grams IS NOT NULL
               AND o.id = (
                   SELECT o2.id FROM orders o2
                   JOIN order_items oi2 ON oi2.order_id = o2.id
                   WHERE o2.user_id = o.user_id AND oi2.product_id = oi.product_id AND o2.status NOT IN ('cancelled')
                   ORDER BY o2.placed_at DESC LIMIT 1
               )",
        );

        $sent = 0;

        foreach ($candidates as $c) {
            $totalDays = (int) round((int) $c['weight_grams'] / (int) $c['feeding_grams_per_day']);
            $runOutDate = (new \DateTimeImmutable((string) $c['placed_at']))->modify("+{$totalDays} days");
            $daysUntilRunOut = (int) (new \DateTimeImmutable('today'))->diff($runOutDate)->format('%r%a');

            if ($daysUntilRunOut > 2 || $daysUntilRunOut < -5) {
                continue; // not due yet, or too stale to bother
            }

            if (Notification::alreadySentFor((int) $c['user_id'], 'replenishment', 'order_item', (int) $c['order_id'])) {
                continue;
            }

            $body = "Hi {$c['user_name']},\n\nBased on your last order, {$c['product_name']} ({$c['variant_label']}) "
                . "should be running low around " . $runOutDate->format('d M') . ".\n\n"
                . "Reorder in one click: " . url('/reorder/' . $c['variant_id']);

            $this->notify((int) $c['user_id'], 'replenishment', 'email', 'Time to reorder ' . $c['product_name'] . '?', $body, 'order_item', (int) $c['order_id']);
            $sent++;
        }

        if ($sent > 0) {
            GrowthAction::log('replenishment', "Sent {$sent} replenishment reminder(s) for food due to run out within 2 days.", ['affected_count' => $sent]);
        }

        return "{$sent} reminder(s) sent";
    }

    /**
     * Triggered by each customer crossing their OWN lapse threshold
     * (avg_order_interval_days * 2, computed in ScoringService) rather than
     * a fixed 90-day rule for everyone.
     */
    public function processWinback(): string
    {
        $db = Database::instance();

        $lapsed = $db->select(
            "SELECT cs.user_id, u.name, u.email FROM customer_scores cs
             JOIN users u ON u.id = cs.user_id
             WHERE cs.churn_risk = 'high'",
        );

        $sent = 0;

        foreach ($lapsed as $row) {
            // Re-notify at most once every 30 days, not on every 15-minute cron tick.
            if ($this->notifiedWithinDays((int) $row['user_id'], 'winback', 30)) {
                continue;
            }

            $couponCode = $this->generateRecoveryCoupon(percent: 15, expiryDays: 7);
            $body = "Hi {$row['name']},\n\nWe miss you! Here's {$couponCode} for 15% off your next order, valid for 7 days.\n\n"
                . url('/shop');

            $this->notify((int) $row['user_id'], 'winback', 'email', 'We miss you at Happy Tails', $body, 'user', (int) $row['user_id']);
            $sent++;
        }

        if ($sent > 0) {
            GrowthAction::log('winback', "Sent {$sent} win-back offer(s) to customers overdue against their own ordering pattern.", ['affected_count' => $sent]);
        }

        return "{$sent} win-back offer(s) sent";
    }

    /**
     * Delivery confirmation + care tips, then a single review request timed
     * after typical usage begins, then at most one reminder — never more.
     */
    public function processPostPurchase(): string
    {
        $db = Database::instance();
        $sent = 0;

        $delivered = $db->select(
            "SELECT o.id, o.user_id, u.name, u.email FROM orders o
             JOIN users u ON u.id = o.user_id
             WHERE o.status = 'delivered' AND o.user_id IS NOT NULL",
        );

        foreach ($delivered as $order) {
            if (!Notification::alreadySentFor((int) $order['user_id'], 'delivery_confirmation', 'order', (int) $order['id'])) {
                $tips = $this->careTipsFor((int) $order['id']);
                $body = "Hi {$order['name']},\n\nYour order has arrived! {$tips}";
                $this->notify((int) $order['user_id'], 'delivery_confirmation', 'email', 'Your order has arrived', $body, 'order', (int) $order['id']);
                $sent++;
                continue; // give it time before the review request
            }

            $deliveryNotif = $db->selectOne(
                "SELECT sent_at FROM notifications WHERE type = 'delivery_confirmation' AND related_type = 'order' AND related_id = :oid",
                ['oid' => $order['id']],
            );
            $daysSinceDelivery = (new \DateTimeImmutable('now'))->diff(new \DateTimeImmutable((string) $deliveryNotif['sent_at']))->days;

            $hasReviewRequest = Notification::alreadySentFor((int) $order['user_id'], 'review_request', 'order', (int) $order['id']);
            $hasReviewReminder = Notification::alreadySentFor((int) $order['user_id'], 'review_reminder', 'order', (int) $order['id']);
            $alreadyReviewed = $this->hasReviewedOrder((int) $order['id']);

            if ($alreadyReviewed) {
                continue;
            }

            if (!$hasReviewRequest && $daysSinceDelivery >= 5) {
                $body = "Hi {$order['name']},\n\nHow's it going so far? A quick review helps other pet owners.\n\n" . url('/account/orders');
                $this->notify((int) $order['user_id'], 'review_request', 'email', 'How are things going?', $body, 'order', (int) $order['id']);
                $sent++;
            } elseif ($hasReviewRequest && !$hasReviewReminder && $daysSinceDelivery >= 12) {
                $body = "Hi {$order['name']},\n\nJust one more nudge — we'd love your review, but this is the last one!\n\n" . url('/account/orders');
                $this->notify((int) $order['user_id'], 'review_reminder', 'email', "Last chance to leave a review", $body, 'order', (int) $order['id']);
                $sent++;
            }
        }

        if ($sent > 0) {
            GrowthAction::log('post_purchase', "Sent {$sent} delivery confirmation/review-request message(s).", ['affected_count' => $sent]);
        }

        return "{$sent} post-purchase message(s) sent";
    }

    /** Birthday coupons and puppy-to-adult transition prompts. */
    public function processPetLifecycle(): string
    {
        $db = Database::instance();
        $sent = 0;

        $birthdays = $db->select(
            "SELECT pt.id AS pet_id, pt.name AS pet_name, pt.user_id, u.name AS user_name, u.email
             FROM pets pt JOIN users u ON u.id = pt.user_id
             WHERE pt.deleted_at IS NULL AND pt.birthday IS NOT NULL
               AND strftime('%m', pt.birthday) = strftime('%m', 'now') AND strftime('%d', pt.birthday) = strftime('%d', 'now')",
        );
        foreach ($birthdays as $pet) {
            if ($this->notifiedWithinDays((int) $pet['user_id'], 'birthday', 300, (int) $pet['pet_id'])) {
                continue;
            }
            $couponCode = $this->generateRecoveryCoupon(percent: 15, expiryDays: 10);
            $body = "Happy birthday to {$pet['pet_name']}! Treat them with {$couponCode} — 15% off, valid 10 days.";
            $this->notify((int) $pet['user_id'], 'birthday', 'email', "It's {$pet['pet_name']}'s birthday!", $body, 'pet', (int) $pet['pet_id']);
            $sent++;
        }

        $transitioning = $db->select(
            "SELECT pt.id AS pet_id, pt.name AS pet_name, pt.species, pt.user_id, u.name AS user_name, u.email
             FROM pets pt JOIN users u ON u.id = pt.user_id
             WHERE pt.deleted_at IS NULL AND pt.species IN ('dog','cat') AND pt.birthday IS NOT NULL
               AND pt.birthday BETWEEN date('now', '-380 days') AND date('now', '-350 days')",
        );
        foreach ($transitioning as $pet) {
            if ($this->notifiedWithinDays((int) $pet['user_id'], 'life_stage_transition', 400, (int) $pet['pet_id'])) {
                continue;
            }
            $body = "{$pet['pet_name']} is turning one! Time to start the switch from puppy/kitten food to an adult formula.\n\n"
                . url('/shop?pet=' . $pet['species'] . '&stage=adult');
            $this->notify((int) $pet['user_id'], 'life_stage_transition', 'email', "{$pet['pet_name']} is ready for adult food", $body, 'pet', (int) $pet['pet_id']);
            $sent++;
        }

        if ($sent > 0) {
            GrowthAction::log('pet_lifecycle', "Sent {$sent} birthday/life-stage message(s) based on pet profiles.", ['affected_count' => $sent]);
        }

        return "{$sent} pet lifecycle message(s) sent";
    }

    private function careTipsFor(int $orderId): string
    {
        $db = Database::instance();
        $petType = $db->selectOne(
            'SELECT p.pet_type FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = :oid LIMIT 1',
            ['oid' => $orderId],
        );

        return match ($petType['pet_type'] ?? 'other') {
            'dog' => 'Tip: introduce new food gradually over 5-7 days mixed with the old to avoid tummy upset.',
            'cat' => 'Tip: keep the water bowl away from the food bowl — most cats actually prefer that.',
            'bird' => 'Tip: replace seed and refresh water daily; stale seed loses nutritional value fast.',
            'fish' => 'Tip: feed small amounts your fish finish within 2 minutes to keep water quality up.',
            default => 'Tip: keep to a consistent feeding schedule — pets do best with routine.',
        };
    }

    private function hasReviewedOrder(int $orderId): bool
    {
        $db = Database::instance();
        $row = $db->selectOne(
            "SELECT 1 AS x FROM reviews r
             JOIN order_items oi ON oi.product_id = r.product_id
             WHERE oi.order_id = :oid AND r.user_id = (SELECT user_id FROM orders WHERE id = :oid2)
             LIMIT 1",
            ['oid' => $orderId, 'oid2' => $orderId],
        );
        return $row !== null;
    }

    private function notifiedWithinDays(int $userId, string $type, int $days, ?int $relatedId = null): bool
    {
        $db = Database::instance();
        $sql = "SELECT 1 AS x FROM notifications WHERE user_id = :uid AND type = :type AND status = 'sent' AND sent_at >= datetime('now', '-{$days} days')";
        $bindings = ['uid' => $userId, 'type' => $type];
        if ($relatedId !== null) {
            $sql .= ' AND related_id = :rid';
            $bindings['rid'] = $relatedId;
        }
        return $db->selectOne($sql, $bindings) !== null;
    }

    private function generateRecoveryCoupon(int $percent = 10, int $expiryDays = 3): string
    {
        $code = 'AUTO' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        Database::instance()->insert('coupons', [
            'code' => $code,
            'description' => 'Auto-generated by the Growth Agent',
            'type' => 'percent',
            'value' => $percent,
            'usage_limit' => 1,
            'usage_limit_per_customer' => 1,
            'expires_at' => (new \DateTimeImmutable("+{$expiryDays} days"))->format('Y-m-d H:i:s'),
            'is_active' => 1,
            'auto_generated' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $code;
    }

    private function notify(int $userId, string $type, string $channel, string $subject, string $body, string $relatedType, int $relatedId): void
    {
        $db = Database::instance();
        $user = $db->selectOne('SELECT name, email FROM users WHERE id = :id', ['id' => $userId]);

        $sent = $channel === 'email' && $user !== null
            ? Mailer::send((string) $user['email'], (string) $user['name'], $subject, nl2br(e($body)))
            : true;

        $db->insert('notifications', [
            'user_id' => $userId,
            'type' => $type,
            'channel' => $channel,
            'subject' => $subject,
            'body' => $body,
            'status' => $sent ? 'sent' : 'failed',
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'sent_at' => $sent ? now() : null,
            'created_at' => now(),
        ]);
    }
}
