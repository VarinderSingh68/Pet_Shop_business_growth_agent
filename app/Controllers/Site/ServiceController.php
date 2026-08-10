<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\App;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Pet;
use App\Models\Service;
use App\Models\ServiceSlot;
use App\Models\StaffMember;
use App\Services\AppointmentService;

final class ServiceController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments = new AppointmentService())
    {
    }

    public function index(Request $request): void
    {
        $this->view('site/services/index', [
            'title' => 'Services',
            'services' => Service::allActive(),
        ]);
    }

    public function show(Request $request, string $slug): void
    {
        $service = Service::findBySlug($slug);
        if ($service === null) {
            abort(404);
        }

        $staff = StaffMember::forService((int) $service['id']);
        $staffId = (int) $request->query('staff', $staff[0]['id'] ?? 0);

        $slots = [];
        if ($staffId > 0) {
            $from = new \DateTimeImmutable('now');
            $to = $from->modify('+21 days');
            $slots = ServiceSlot::availableFor((int) $service['id'], $staffId, $from->format('Y-m-d'), $to->format('Y-m-d'));
        }

        $slotsByDay = [];
        foreach ($slots as $slot) {
            $day = (new \DateTimeImmutable((string) $slot['start_at']))->format('Y-m-d');
            $slotsByDay[$day][] = $slot;
        }

        $user = App::auth()->user();

        $this->view('site/services/show', [
            'title' => $service['name'],
            'description' => $service['description'],
            'service' => $service,
            'staff' => $staff,
            'selectedStaffId' => $staffId,
            'slotsByDay' => $slotsByDay,
            'pets' => $user !== null ? Pet::forUser((int) $user['id']) : [],
        ]);
    }

    public function book(Request $request): void
    {
        $user = App::auth()->user();
        $data = $request->only(['service_id', 'staff_id', 'slot_id', 'pet_id', 'guest_name', 'guest_email', 'guest_phone', 'notes']);

        $rules = [
            'service_id' => 'required|integer',
            'staff_id' => 'required|integer',
            'slot_id' => 'required|integer',
        ];
        if ($user === null) {
            $rules['guest_name'] = 'required|max:150';
            $rules['guest_email'] = 'required|email';
            $rules['guest_phone'] = 'required|max:20';
        }

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            flash('error', $validator->firstError() ?? 'Please fill in all required fields.');
            back();
        }

        $service = \App\Models\Service::find((int) $data['service_id']);
        if ($service === null) {
            abort(404);
        }

        try {
            $appointment = $this->appointments->book(
                (int) $data['slot_id'],
                (int) $data['service_id'],
                (int) $data['staff_id'],
                $user['id'] ?? null,
                !empty($data['pet_id']) ? (int) $data['pet_id'] : null,
                $data['guest_name'],
                $data['guest_email'],
                $data['guest_phone'],
                !empty($data['notes']) ? $data['notes'] : null,
                $service['deposit_paise'] !== null ? (int) $service['deposit_paise'] : null,
            );
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            back();
        }

        flash('success', 'Booked! Confirmation number ' . $appointment['booking_number'] . '.');
        $this->redirect('/services/booking/' . $appointment['booking_number']);
    }

    public function confirmation(Request $request, string $bookingNumber): void
    {
        $appointment = \App\Models\Appointment::findByBookingNumber($bookingNumber);
        if ($appointment === null) {
            abort(404);
        }

        $details = \App\Models\Appointment::withDetails((int) $appointment['id']);

        $this->view('site/services/confirmation', [
            'title' => 'Booking confirmed',
            'appointment' => $details,
        ]);
    }

    public function cancel(Request $request, string $id): void
    {
        $user = App::auth()->user();
        $appointment = \App\Models\Appointment::find((int) $id);

        if ($appointment === null || $user === null || (int) $appointment['user_id'] !== (int) $user['id']) {
            abort(404);
        }

        try {
            $this->appointments->cancel((int) $id);
            flash('success', 'Appointment cancelled.');
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
        }

        $this->redirect('/account/appointments');
    }
}
