<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE enquiries (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NULL,
                name VARCHAR(150) NOT NULL,
                email VARCHAR(191) NOT NULL,
                phone VARCHAR(20) NULL,
                subject VARCHAR(200) NOT NULL,
                message TEXT NOT NULL,
                order_number VARCHAR(30) NULL,
                status ENUM('open','in_progress','resolved') NOT NULL DEFAULT 'open',
                staff_reply TEXT NULL,
                replied_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY enquiries_user_id_index (user_id),
                KEY enquiries_status_index (status),
                CONSTRAINT enquiries_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS enquiries');
    }
};
