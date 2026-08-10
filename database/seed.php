<?php

declare(strict_types=1);

use App\Core\App;

$isEntry = realpath($argv[0] ?? '') === __FILE__;

if ($isEntry) {
    require dirname(__DIR__) . '/vendor/autoload.php';
    App::bootstrap(cli: true);
}

echo "Seeding: roles and permissions\n";
$roleIds = require __DIR__ . '/seeds/001_roles_and_permissions.php';

echo "Seeding: base accounts\n";
require __DIR__ . '/seeds/002_base_accounts.php';

echo "Seeding: catalogue demo data\n";
require __DIR__ . '/seeds/003_catalogue_demo.php';

echo "Seeding: coupons\n";
require __DIR__ . '/seeds/004_coupons_demo.php';

echo "Seeding: services and staff\n";
require __DIR__ . '/seeds/005_services_demo.php';

echo "Seeding: content (pages, FAQs, blog, testimonials)\n";
require __DIR__ . '/seeds/006_content_demo.php';

echo "Seeding: catalogue expansion\n";
require __DIR__ . '/seeds/007_catalogue_expansion.php';

echo "Seeding: demo customers, pets, and order history\n";
require __DIR__ . '/seeds/008_demo_customers_and_orders.php';

echo "Seeding: reviews and appointments\n";
require __DIR__ . '/seeds/009_reviews_and_appointments.php';

if ($isEntry) {
    echo "\nDone.\n";
}
