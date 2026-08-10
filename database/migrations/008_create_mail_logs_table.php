<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE mail_logs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                to_email VARCHAR(191) NOT NULL,
                to_name VARCHAR(150) NULL,
                subject VARCHAR(255) NOT NULL,
                body_html LONGTEXT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT 'logged',
                error VARCHAR(500) NULL,
                created_at DATETIME NOT NULL,
                KEY mail_logs_to_email_index (to_email),
                KEY mail_logs_created_at_index (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS mail_logs');
    }
};
