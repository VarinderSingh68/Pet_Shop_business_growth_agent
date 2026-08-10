<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            `key` VARCHAR(150) NOT NULL,
            `value` TEXT NULL,
            `type` VARCHAR(20) NOT NULL DEFAULT 'string',
            `group` VARCHAR(100) NOT NULL DEFAULT 'general',
            updated_at DATETIME NOT NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX settings_key_unique ON settings(`key`)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS settings');
    }
};
