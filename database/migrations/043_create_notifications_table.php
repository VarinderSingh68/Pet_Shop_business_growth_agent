<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE notifications (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NULL,
                type VARCHAR(50) NOT NULL COMMENT 'e.g. abandoned_cart_1, replenishment, birthday, review_request, winback',
                channel ENUM('email','sms','whatsapp','banner','system') NOT NULL DEFAULT 'email',
                subject VARCHAR(200) NULL,
                body TEXT NULL,
                status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
                related_type VARCHAR(50) NULL,
                related_id INT UNSIGNED NULL,
                sent_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                KEY notifications_user_id_index (user_id),
                KEY notifications_type_index (type),
                KEY notifications_related_index (related_type, related_id),
                CONSTRAINT notifications_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS notifications');
    }
};
