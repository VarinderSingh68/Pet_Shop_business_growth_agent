<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ServiceSlot extends Model
{
    protected static string $table = 'service_slots';
    protected static bool $timestamps = false;

    /** @return array<int, array<string, mixed>> */
    public static function availableFor(int $serviceId, int $staffId, string $fromDate, string $toDate): array
    {
        return static::db()->select(
            'SELECT * FROM service_slots
             WHERE service_id = :sid AND staff_id = :stid AND is_booked = 0
               AND start_at >= :from AND start_at < :to
             ORDER BY start_at ASC',
            ['sid' => $serviceId, 'stid' => $staffId, 'from' => $fromDate, 'to' => $toDate],
        );
    }
}
