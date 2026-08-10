<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE activity_logs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NULL,
                action VARCHAR(150) NOT NULL,
                subject_type VARCHAR(100) NULL,
                subject_id INT UNSIGNED NULL,
                description VARCHAR(500) NULL,
                properties JSON NULL,
                ip_address VARCHAR(45) NULL,
                created_at DATETIME NOT NULL,
                KEY activity_logs_user_id_index (user_id),
                KEY activity_logs_subject_index (subject_type, subject_id),
                KEY activity_logs_created_at_index (created_at),
                CONSTRAINT activity_logs_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS activity_logs');
    }
};
