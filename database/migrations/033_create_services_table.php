<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(150) NOT NULL,
            slug VARCHAR(160) NOT NULL,
            category TEXT NOT NULL CHECK (category IN ('grooming','boarding','vet','training')),
            description VARCHAR(500) NULL,
            duration_minutes INT NOT NULL DEFAULT 60,
            price_paise INT NOT NULL,
            deposit_paise INT NULL,
            reschedule_cutoff_hours INT NOT NULL DEFAULT 24,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX services_slug_unique ON services(slug)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS services');
    }
};
