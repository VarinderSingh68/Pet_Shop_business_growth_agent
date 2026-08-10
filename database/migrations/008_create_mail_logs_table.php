<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE mail_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            to_email VARCHAR(191) NOT NULL,
            to_name VARCHAR(150) NULL,
            subject VARCHAR(255) NOT NULL,
            body_html TEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'logged',
            error VARCHAR(500) NULL,
            created_at DATETIME NOT NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX mail_logs_to_email_index ON mail_logs(to_email)');
        $pdo->exec('CREATE INDEX mail_logs_created_at_index ON mail_logs(created_at)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS mail_logs');
    }
};
