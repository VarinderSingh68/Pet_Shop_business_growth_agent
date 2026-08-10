<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE settings (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `key` VARCHAR(150) NOT NULL,
                `value` TEXT NULL,
                `type` VARCHAR(20) NOT NULL DEFAULT 'string',
                `group` VARCHAR(100) NOT NULL DEFAULT 'general',
                updated_at DATETIME NOT NULL,
                UNIQUE KEY settings_key_unique (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS settings');
    }
};
