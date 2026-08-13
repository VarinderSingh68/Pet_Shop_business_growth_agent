<?php

declare(strict_types=1);

use App\Core\Migrator;

require dirname(__DIR__) . '/vendor/autoload.php';

$testDbPath = sys_get_temp_dir() . '/pet_shop_test_' . getmypid() . '.sqlite';

putenv('APP_ENV=testing');
putenv('APP_DEBUG=false');
putenv('DB_PATH=' . $testDbPath);
$_ENV['DB_PATH'] = $testDbPath;
$_SERVER['DB_PATH'] = $testDbPath;

register_shutdown_function(static function () use ($testDbPath): void {
    foreach (['', '-wal', '-shm'] as $suffix) {
        @unlink($testDbPath . $suffix);
    }
});

(new Migrator(dirname(__DIR__) . '/database/migrations'))->up();
