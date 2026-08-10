<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class StaffMember extends Model
{
    protected static string $table = 'staff_members';

    /** @return array<int, array<string, mixed>> */
    public static function forService(int $serviceId): array
    {
        return static::db()->select(
            'SELECT sm.* FROM staff_members sm
             JOIN service_staff ss ON ss.staff_id = sm.id
             WHERE ss.service_id = :sid AND sm.is_active = 1
             ORDER BY sm.name',
            ['sid' => $serviceId],
        );
    }

    /** @return array<int, string> */
    public static function blackoutDates(int $staffId): array
    {
        $rows = static::db()->select(
            'SELECT date FROM staff_blackout_dates WHERE staff_id = :sid',
            ['sid' => $staffId],
        );
        return array_map(static fn (array $r) => $r['date'], $rows);
    }
}
