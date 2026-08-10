<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE loyalty_points (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                points INT NOT NULL COMMENT 'Signed: positive = earned, negative = redeemed/expired',
                type ENUM('earned','redeemed','expired','adjusted') NOT NULL,
                reference_type VARCHAR(50) NULL,
                reference_id INT UNSIGNED NULL,
                note VARCHAR(255) NULL,
                expires_at DATE NULL,
                created_at DATETIME NOT NULL,
                KEY loyalty_points_user_id_index (user_id),
                KEY loyalty_points_expires_at_index (expires_at),
                CONSTRAINT loyalty_points_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS loyalty_points');
    }
};
