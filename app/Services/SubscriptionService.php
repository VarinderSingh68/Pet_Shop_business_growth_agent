<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Subscription;

final class SubscriptionService
{
    public function create(int $userId, int $productId, int $variantId, int $quantity, int $intervalDays, ?int $addressId): array
    {
        $id = Database::instance()->insert('subscriptions', [
            'user_id' => $userId,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'address_id' => $addressId,
            'quantity' => $quantity,
            'interval_days' => $intervalDays,
            'status' => 'active',
            'next_order_date' => (new \DateTimeImmutable("+{$intervalDays} days"))->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Subscription::find($id);
    }

    public function pause(int $subscriptionId, int $userId): void
    {
        $this->assertOwnership($subscriptionId, $userId);
        Subscription::updateWhere($subscriptionId, ['status' => 'paused']);
    }

    public function resume(int $subscriptionId, int $userId): void
    {
        $sub = $this->assertOwnership($subscriptionId, $userId);

        $next = max(
            (new \DateTimeImmutable('now'))->modify('+1 day'),
            new \DateTimeImmutable((string) $sub['next_order_date']),
        );

        Subscription::updateWhere($subscriptionId, [
            'status' => 'active',
            'next_order_date' => $next->format('Y-m-d'),
        ]);
    }

    public function skipNext(int $subscriptionId, int $userId): void
    {
        $sub = $this->assertOwnership($subscriptionId, $userId);

        $next = (new \DateTimeImmutable((string) $sub['next_order_date']))
            ->modify('+' . (int) $sub['interval_days'] . ' days');

        Subscription::updateWhere($subscriptionId, ['next_order_date' => $next->format('Y-m-d')]);
    }

    public function cancel(int $subscriptionId, int $userId): void
    {
        $this->assertOwnership($subscriptionId, $userId);
        Subscription::updateWhere($subscriptionId, ['status' => 'cancelled', 'cancelled_at' => now()]);
    }

    private function assertOwnership(int $subscriptionId, int $userId): array
    {
        $sub = Subscription::find($subscriptionId);
        if ($sub === null || (int) $sub['user_id'] !== $userId) {
            throw new \RuntimeException('Subscription not found.');
        }
        return $sub;
    }
}
