<?php

declare(strict_types=1);

use App\Core\Database;
use App\Services\AppointmentService;

$db = Database::instance();

$standardHours = json_encode([
    'mon' => ['10:00', '18:00'], 'tue' => ['10:00', '18:00'], 'wed' => ['10:00', '18:00'],
    'thu' => ['10:00', '18:00'], 'fri' => ['10:00', '18:00'], 'sat' => ['10:00', '16:00'],
]);

$staffList = [
    ['name' => 'Ananya Rao', 'title' => 'Senior Groomer', 'bio' => 'Eight years grooming everything from poodles to Persians.'],
    ['name' => 'Dr. Vikram Iyer', 'title' => 'Resident Vet', 'bio' => 'BVSc, focuses on preventive wellness and vaccination care.'],
];

$staffIds = [];
foreach ($staffList as $s) {
    $existing = $db->selectOne('SELECT id FROM staff_members WHERE name = :n', ['n' => $s['name']]);
    if ($existing !== null) {
        $staffIds[$s['name']] = (int) $existing['id'];
        continue;
    }
    $staffIds[$s['name']] = $db->insert('staff_members', [
        'name' => $s['name'],
        'title' => $s['title'],
        'bio' => $s['bio'],
        'working_hours' => $standardHours,
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

$services = [
    ['name' => 'Bath & Full Groom', 'category' => 'grooming', 'duration' => 60, 'price' => 89900, 'deposit' => null,
     'description' => 'Shampoo, blow-dry, trim, nail clipping, and ear cleaning.', 'staff' => ['Ananya Rao']],
    ['name' => 'Wellness Checkup', 'category' => 'vet', 'duration' => 30, 'price' => 59900, 'deposit' => 20000,
     'description' => 'General health checkup with our resident vet, vaccination review included.', 'staff' => ['Dr. Vikram Iyer']],
    ['name' => 'Overnight Boarding (per night)', 'category' => 'boarding', 'duration' => 30, 'price' => 129900, 'deposit' => 50000,
     'description' => 'Supervised overnight stay with feeding, walks, and a comfy kennel.', 'staff' => ['Ananya Rao', 'Dr. Vikram Iyer']],
];

$appointmentService = new AppointmentService();
$created = 0;
$slotsGenerated = 0;

foreach ($services as $svc) {
    $slug = slugify($svc['name']);
    $existing = $db->selectOne('SELECT id FROM services WHERE slug = :slug', ['slug' => $slug]);

    if ($existing === null) {
        $serviceId = $db->insert('services', [
            'name' => $svc['name'],
            'slug' => $slug,
            'category' => $svc['category'],
            'description' => $svc['description'],
            'duration_minutes' => $svc['duration'],
            'price_paise' => $svc['price'],
            'deposit_paise' => $svc['deposit'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $created++;
    } else {
        $serviceId = (int) $existing['id'];
    }

    foreach ($svc['staff'] as $staffName) {
        $staffId = $staffIds[$staffName];
        $exists = $db->selectOne('SELECT 1 AS x FROM service_staff WHERE service_id = :s AND staff_id = :st', ['s' => $serviceId, 'st' => $staffId]);
        if ($exists === null) {
            $db->insert('service_staff', ['service_id' => $serviceId, 'staff_id' => $staffId]);
        }

        $staff = $db->selectOne('SELECT * FROM staff_members WHERE id = :id', ['id' => $staffId]);
        $service = $db->selectOne('SELECT * FROM services WHERE id = :id', ['id' => $serviceId]);
        $slotsGenerated += $appointmentService->generateSlots($staff, $service, 21);
    }
}

echo "  Services: {$created} created, {$slotsGenerated} slots generated.\n";
