<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE feature_flags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            `key` VARCHAR(100) NOT NULL,
            description VARCHAR(255) NULL,
            is_enabled INTEGER NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX feature_flags_key_unique ON feature_flags(`key`)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS feature_flags');
    }
};
