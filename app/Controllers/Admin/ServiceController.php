<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Service;
use App\Models\StaffMember;

final class ServiceController extends Controller
{
    // --- Staff roster -----------------------------------------------------

    public function staff(Request $request): void
    {
        $this->view('admin/services/staff', [
            'title' => 'Staff roster',
            'staff' => Database::instance()->select(
                "SELECT sm.*, (SELECT COUNT(*) FROM appointments a JOIN service_slots sl ON sl.id = a.slot_id
                               WHERE a.staff_id = sm.id AND a.status = 'no_show') AS no_show_count
                 FROM staff_members sm ORDER BY sm.name",
            ),
        ]);
    }

    public function createStaff(Request $request): void
    {
        $this->view('admin/services/staff-form', ['title' => 'New staff member', 'staff' => null]);
    }

    public function editStaff(Request $request, string $id): void
    {
        $staff = StaffMember::find((int) $id);
        if ($staff === null) {
            abort(404);
        }

        $this->view('admin/services/staff-form', [
            'title' => 'Edit ' . $staff['name'],
            'staff' => $staff,
            'blackoutDates' => Database::instance()->select(
                'SELECT * FROM staff_blackout_dates WHERE staff_id = :id ORDER BY date ASC',
                ['id' => $id],
            ),
        ]);
    }

    public function storeStaff(Request $request): void
    {
        $data = $this->validatedStaffData($request);
        $id = StaffMember::create($data);
        flash('success', 'Staff member added.');
        $this->redirect('/admin/services/staff/' . $id . '/edit');
    }

    public function updateStaff(Request $request, string $id): void
    {
        StaffMember::updateWhere((int) $id, $this->validatedStaffData($request));
        flash('success', 'Staff member updated.');
        $this->redirect('/admin/services/staff/' . $id . '/edit');
    }

    private function validatedStaffData(Request $request): array
    {
        $data = $request->only(['name', 'title', 'bio', 'is_active']);
        $validator = Validator::make($data, ['name' => 'required|max:150']);
        if ($validator->fails()) {
            flash('error', 'Please enter a name.');
            back();
        }

        $hours = [];
        foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $day) {
            $start = $request->input("hours_{$day}_start");
            $end = $request->input("hours_{$day}_end");
            if (!empty($start) && !empty($end)) {
                $hours[$day] = ['start' => $start, 'end' => $end];
            }
        }

        return [
            'name' => $data['name'],
            'title' => !empty($data['title']) ? $data['title'] : null,
            'bio' => !empty($data['bio']) ? $data['bio'] : null,
            'working_hours' => json_encode($hours),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ];
    }

    public function storeBlackoutDate(Request $request, string $staffId): void
    {
        $date = (string) $request->input('date', '');
        if ($date !== '') {
            Database::instance()->insert('staff_blackout_dates', [
                'staff_id' => $staffId,
                'date' => $date,
                'reason' => (string) $request->input('reason', '') ?: null,
                'created_at' => now(),
            ]);
        }
        $this->redirect('/admin/services/staff/' . $staffId . '/edit');
    }

    public function destroyBlackoutDate(Request $request, string $staffId, string $id): void
    {
        Database::instance()->delete('staff_blackout_dates', 'id = :id AND staff_id = :sid', ['id' => $id, 'sid' => $staffId]);
        $this->redirect('/admin/services/staff/' . $staffId . '/edit');
    }

    // --- Services catalogue -----------------------------------------------

    public function services(Request $request): void
    {
        $this->view('admin/services/services', [
            'title' => 'Services',
            'services' => Service::all('name ASC'),
        ]);
    }

    public function createService(Request $request): void
    {
        $this->view('admin/services/service-form', [
            'title' => 'New service',
            'service' => null,
            'allStaff' => StaffMember::where(['is_active' => 1], 'name ASC'),
            'assignedStaffIds' => [],
        ]);
    }

    public function editService(Request $request, string $id): void
    {
        $service = Service::find((int) $id);
        if ($service === null) {
            abort(404);
        }

        $assigned = Database::instance()->select('SELECT staff_id FROM service_staff WHERE service_id = :id', ['id' => $id]);

        $this->view('admin/services/service-form', [
            'title' => 'Edit ' . $service['name'],
            'service' => $service,
            'allStaff' => StaffMember::where(['is_active' => 1], 'name ASC'),
            'assignedStaffIds' => array_column($assigned, 'staff_id'),
        ]);
    }

    public function storeService(Request $request): void
    {
        $data = $this->validatedServiceData($request);
        $data['slug'] = slugify($data['name']);
        $id = Service::create($data);
        $this->syncServiceStaff($request, (int) $id);

        flash('success', 'Service created.');
        $this->redirect('/admin/services/services/' . $id . '/edit');
    }

    public function updateService(Request $request, string $id): void
    {
        Service::updateWhere((int) $id, $this->validatedServiceData($request));
        $this->syncServiceStaff($request, (int) $id);

        flash('success', 'Service updated.');
        $this->redirect('/admin/services/services/' . $id . '/edit');
    }

    private function syncServiceStaff(Request $request, int $serviceId): void
    {
        $staffIds = array_map('intval', (array) $request->input('staff_ids', []));
        $db = Database::instance();
        $db->delete('service_staff', 'service_id = :id', ['id' => $serviceId]);
        foreach ($staffIds as $staffId) {
            $db->insert('service_staff', ['service_id' => $serviceId, 'staff_id' => $staffId]);
        }
    }

    private function validatedServiceData(Request $request): array
    {
        $data = $request->only(['name', 'category', 'description', 'duration_minutes', 'price', 'deposit', 'reschedule_cutoff_hours', 'is_active']);
        $validator = Validator::make($data, [
            'name' => 'required|max:150',
            'category' => 'required|in:grooming,boarding,vet,training',
            'duration_minutes' => 'required|numeric',
            'price' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            flash('error', $validator->firstError() ?? 'Please check the service details.');
            back();
        }

        return [
            'name' => $data['name'],
            'category' => $data['category'],
            'description' => !empty($data['description']) ? $data['description'] : null,
            'duration_minutes' => (int) $data['duration_minutes'],
            'price_paise' => (int) round(((float) $data['price']) * 100),
            'deposit_paise' => !empty($data['deposit']) ? (int) round(((float) $data['deposit']) * 100) : null,
            'reschedule_cutoff_hours' => (int) ($data['reschedule_cutoff_hours'] ?? 24),
            'is_active' => !empty($data['is_active']) ? 1 : 0,
        ];
    }

    // --- Appointments calendar/list -----------------------------------

    public function appointments(Request $request): void
    {
        $date = (string) $request->query('date', date('Y-m-d'));
        $staffId = $request->query('staff_id');

        $where = ['DATE(sl.start_at) = :date'];
        $bindings = ['date' => $date];
        if (!empty($staffId)) {
            $where[] = 'a.staff_id = :staff_id';
            $bindings['staff_id'] = $staffId;
        }
        $whereSql = implode(' AND ', $where);

        $appointments = Database::instance()->select(
            "SELECT a.*, s.name AS service_name, sm.name AS staff_name, sl.start_at, sl.end_at,
                    u.name AS user_name, p.name AS pet_name
             FROM appointments a
             JOIN services s ON s.id = a.service_id
             JOIN staff_members sm ON sm.id = a.staff_id
             JOIN service_slots sl ON sl.id = a.slot_id
             LEFT JOIN users u ON u.id = a.user_id
             LEFT JOIN pets p ON p.id = a.pet_id
             WHERE {$whereSql}
             ORDER BY sl.start_at ASC",
            $bindings,
        );

        $this->view('admin/services/appointments', [
            'title' => 'Appointments',
            'appointments' => $appointments,
            'date' => $date,
            'staffId' => $staffId,
            'allStaff' => StaffMember::where(['is_active' => 1], 'name ASC'),
        ]);
    }

    public function updateAppointmentStatus(Request $request, string $id): void
    {
        $status = (string) $request->input('status');
        if (!in_array($status, ['booked', 'confirmed', 'completed', 'cancelled', 'no_show'], true)) {
            abort(404);
        }

        Database::instance()->update('appointments', [
            'status' => $status,
            'cancelled_at' => $status === 'cancelled' ? now() : null,
            'updated_at' => now(),
        ], 'id = :id', ['id' => $id]);

        // A cancellation frees the slot back up for booking.
        if ($status === 'cancelled') {
            $appointment = Database::instance()->selectOne('SELECT slot_id FROM appointments WHERE id = :id', ['id' => $id]);
            if ($appointment !== null) {
                Database::instance()->update('service_slots', ['is_booked' => 0], 'id = :id', ['id' => $appointment['slot_id']]);
            }
        }

        flash('success', 'Appointment updated.');
        back();
    }

    public function rescheduleAppointment(Request $request, string $id): void
    {
        $newSlotId = (int) $request->input('slot_id');
        $appointment = Database::instance()->selectOne('SELECT * FROM appointments WHERE id = :id', ['id' => $id]);
        if ($appointment === null) {
            abort(404);
        }

        $db = Database::instance();
        $db->transaction(function (Database $db) use ($appointment, $newSlotId, $id) {
            $newSlot = $db->selectOne('SELECT * FROM service_slots WHERE id = :id AND is_booked = 0', ['id' => $newSlotId]);
            if ($newSlot === null) {
                throw new \RuntimeException('That slot is no longer available.');
            }

            $db->update('service_slots', ['is_booked' => 0], 'id = :id', ['id' => $appointment['slot_id']]);
            $db->update('service_slots', ['is_booked' => 1], 'id = :id', ['id' => $newSlotId]);
            $db->update('appointments', ['slot_id' => $newSlotId, 'status' => 'confirmed', 'updated_at' => now()], 'id = :id', ['id' => $id]);
        });

        flash('success', 'Appointment rescheduled.');
        $this->redirect('/admin/services/appointments');
    }
}
