<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Appointment extends Model
{
    protected static string $table = 'appointments';

    public static function findByBookingNumber(string $number): ?array
    {
        return static::firstWhere(['booking_number' => strtoupper(trim($number))]);
    }

    public static function generateBookingNumber(): string
    {
        return 'APT-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }

    /** @return array<int, array<string, mixed>> */
    public static function forUser(int $userId): array
    {
        return static::db()->select(
            'SELECT a.*, s.name AS service_name, s.duration_minutes, sm.name AS staff_name, sl.start_at, sl.end_at
             FROM appointments a
             JOIN services s ON s.id = a.service_id
             JOIN staff_members sm ON sm.id = a.staff_id
             JOIN service_slots sl ON sl.id = a.slot_id
             WHERE a.user_id = :uid
             ORDER BY sl.start_at DESC',
            ['uid' => $userId],
        );
    }

    /** @return array<string, mixed>|null */
    public static function withDetails(int $id): ?array
    {
        return static::db()->selectOne(
            'SELECT a.*, s.name AS service_name, s.duration_minutes, s.reschedule_cutoff_hours,
                    sm.name AS staff_name, sl.start_at, sl.end_at
             FROM appointments a
             JOIN services s ON s.id = a.service_id
             JOIN staff_members sm ON sm.id = a.staff_id
             JOIN service_slots sl ON sl.id = a.slot_id
             WHERE a.id = :id',
            ['id' => $id],
        );
    }
}
