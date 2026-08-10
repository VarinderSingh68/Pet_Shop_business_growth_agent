<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE feature_flags (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `key` VARCHAR(100) NOT NULL,
                description VARCHAR(255) NULL,
                is_enabled TINYINT(1) NOT NULL DEFAULT 0,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY feature_flags_key_unique (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS feature_flags');
    }
};
