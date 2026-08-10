<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE services (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                slug VARCHAR(160) NOT NULL,
                category ENUM('grooming','boarding','vet','training') NOT NULL,
                description VARCHAR(500) NULL,
                duration_minutes INT UNSIGNED NOT NULL DEFAULT 60,
                price_paise INT UNSIGNED NOT NULL,
                deposit_paise INT UNSIGNED NULL,
                reschedule_cutoff_hours INT UNSIGNED NOT NULL DEFAULT 24,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY services_slug_unique (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS services');
    }
};
