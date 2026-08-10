<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Migrator;

require dirname(__DIR__) . '/vendor/autoload.php';

App::bootstrap(cli: true);

$migrator = new Migrator(__DIR__ . '/migrations');

$command = $argv[1] ?? 'status';
$seedAfter = in_array('--seed', $argv, true);

switch ($command) {
    case 'status':
        echo "Migration status:\n";
        foreach ($migrator->status() as $row) {
            echo '  [' . ($row['ran'] ? 'X' : ' ') . '] ' . $row['migration'] . "\n";
        }
        break;

    case 'up':
        $applied = $migrator->up();
        if ($applied === []) {
            echo "Nothing to migrate. Already up to date.\n";
        } else {
            foreach ($applied as $name) {
                echo "Migrated: {$name}\n";
            }
        }
        break;

    case 'down':
        $rolledBack = $migrator->down();
        if ($rolledBack === []) {
            echo "Nothing to roll back.\n";
        } else {
            foreach ($rolledBack as $name) {
                echo "Rolled back: {$name}\n";
            }
        }
        break;

    case 'fresh':
        echo "Dropping all tables and re-running migrations...\n";
        $applied = $migrator->fresh();
        foreach ($applied as $name) {
            echo "Migrated: {$name}\n";
        }
        break;

    default:
        fwrite(STDERR, "Unknown command [{$command}]. Use: status|up|down|fresh [--seed]\n");
        exit(1);
}

if ($seedAfter && in_array($command, ['up', 'fresh'], true)) {
    echo "\nSeeding database...\n";
    require __DIR__ . '/seed.php';
}
