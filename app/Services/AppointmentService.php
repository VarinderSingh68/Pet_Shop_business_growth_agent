<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Appointment;
use App\Models\StaffMember;

final class AppointmentService
{
    /**
     * Generates bookable slots for a staff+service pair across the given
     * number of days, respecting working hours and blackout dates. Safe to
     * re-run — the (staff_id, start_at) unique key skips ones that exist.
     */
    public function generateSlots(array $staff, array $service, int $days = 21): int
    {
        $db = Database::instance();
        $hours = json_decode((string) ($staff['working_hours'] ?? '{}'), true) ?: [];
        $blackout = StaffMember::blackoutDates((int) $staff['id']);
        $duration = (int) $service['duration_minutes'];
        $created = 0;

        $dayKeys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

        for ($d = 1; $d <= $days; $d++) {
            $date = (new \DateTimeImmutable("+{$d} days"))->format('Y-m-d');
            if (in_array($date, $blackout, true)) {
                continue;
            }

            $weekday = $dayKeys[(int) (new \DateTimeImmutable($date))->format('w')];
            $window = $hours[$weekday] ?? null;
            if ($window === null) {
                continue;
            }

            [$openTime, $closeTime] = $window;
            $cursor = new \DateTimeImmutable("{$date} {$openTime}");
            $close = new \DateTimeImmutable("{$date} {$closeTime}");

            while ($cursor->modify("+{$duration} minutes") <= $close) {
                $start = $cursor;
                $end = $cursor->modify("+{$duration} minutes");

                $exists = $db->selectOne(
                    'SELECT id FROM service_slots WHERE staff_id = :sid AND start_at = :start',
                    ['sid' => $staff['id'], 'start' => $start->format('Y-m-d H:i:s')],
                );

                if ($exists === null) {
                    $db->insert('service_slots', [
                        'service_id' => $service['id'],
                        'staff_id' => $staff['id'],
                        'start_at' => $start->format('Y-m-d H:i:s'),
                        'end_at' => $end->format('Y-m-d H:i:s'),
                        'is_booked' => 0,
                        'created_at' => now(),
                    ]);
                    $created++;
                }

                $cursor = $end;
            }
        }

        return $created;
    }

    /**
     * Books a slot transactionally — locks the slot row so two customers
     * can't both book the same window, then creates the appointment.
     *
     * @throws \RuntimeException if the slot is no longer available
     */
    public function book(
        int $slotId,
        int $serviceId,
        int $staffId,
        ?int $userId,
        ?int $petId,
        ?string $guestName,
        ?string $guestEmail,
        ?string $guestPhone,
        ?string $notes,
        ?int $depositPaise,
    ): array {
        $db = Database::instance();

        return $db->transaction(function (Database $db) use ($slotId, $serviceId, $staffId, $userId, $petId, $guestName, $guestEmail, $guestPhone, $notes, $depositPaise) {
            $slot = $db->selectOne('SELECT * FROM service_slots WHERE id = :id', ['id' => $slotId]);

            if ($slot === null || (int) $slot['is_booked'] === 1) {
                throw new \RuntimeException('That time slot was just booked by someone else. Please choose another.');
            }

            $db->update('service_slots', ['is_booked' => 1], 'id = :id', ['id' => $slotId]);

            $appointmentId = $db->insert('appointments', [
                'booking_number' => Appointment::generateBookingNumber(),
                'service_id' => $serviceId,
                'staff_id' => $staffId,
                'slot_id' => $slotId,
                'user_id' => $userId,
                'pet_id' => $petId,
                'guest_name' => $userId === null ? $guestName : null,
                'guest_email' => $userId === null ? $guestEmail : null,
                'guest_phone' => $userId === null ? $guestPhone : null,
                'status' => 'booked',
                'payment_status' => $depositPaise !== null ? 'pending' : 'not_required',
                'deposit_paise' => $depositPaise,
                'customer_notes' => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return Appointment::find($appointmentId);
        });
    }

    public function canModify(array $appointment): bool
    {
        if (!in_array($appointment['status'], ['booked', 'confirmed'], true)) {
            return false;
        }

        $cutoff = (new \DateTimeImmutable((string) $appointment['start_at']))
            ->modify('-' . (int) $appointment['reschedule_cutoff_hours'] . ' hours');

        return new \DateTimeImmutable('now') < $cutoff;
    }

    /** @throws \RuntimeException if outside the reschedule/cancel policy window */
    public function cancel(int $appointmentId): void
    {
        $db = Database::instance();
        $appointment = Appointment::withDetails($appointmentId);

        if ($appointment === null) {
            throw new \RuntimeException('Appointment not found.');
        }

        if (!$this->canModify($appointment)) {
            throw new \RuntimeException(
                'This appointment can no longer be cancelled online — it\'s within '
                . $appointment['reschedule_cutoff_hours'] . ' hours of the start time. Please call the store.',
            );
        }

        $db->update('service_slots', ['is_booked' => 0], 'id = :id', ['id' => $appointment['slot_id']]);
        $db->update('appointments', ['status' => 'cancelled', 'cancelled_at' => now()], 'id = :id', ['id' => $appointmentId]);
    }

    /** @throws \RuntimeException if outside policy or the new slot is unavailable */
    public function reschedule(int $appointmentId, int $newSlotId): array
    {
        $db = Database::instance();

        return $db->transaction(function (Database $db) use ($appointmentId, $newSlotId) {
            $appointment = Appointment::withDetails($appointmentId);
            if ($appointment === null) {
                throw new \RuntimeException('Appointment not found.');
            }
            if (!$this->canModify($appointment)) {
                throw new \RuntimeException(
                    'This appointment is too close to its start time to reschedule online. Please call the store.',
                );
            }

            $newSlot = $db->selectOne('SELECT * FROM service_slots WHERE id = :id', ['id' => $newSlotId]);
            if ($newSlot === null || (int) $newSlot['is_booked'] === 1) {
                throw new \RuntimeException('That time slot is no longer available.');
            }

            $db->update('service_slots', ['is_booked' => 0], 'id = :id', ['id' => $appointment['slot_id']]);
            $db->update('service_slots', ['is_booked' => 1], 'id = :id', ['id' => $newSlotId]);
            $db->update('appointments', ['slot_id' => $newSlotId, 'status' => 'booked'], 'id = :id', ['id' => $appointmentId]);

            return Appointment::withDetails($appointmentId);
        });
    }
}
