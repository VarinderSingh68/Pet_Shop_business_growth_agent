<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE password_resets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email VARCHAR(191) NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            created_at DATETIME NOT NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX password_resets_email_index ON password_resets(email)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS password_resets');
    }
};
