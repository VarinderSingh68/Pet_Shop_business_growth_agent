<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE shipments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_id INT UNSIGNED NOT NULL,
                carrier VARCHAR(100) NULL,
                tracking_number VARCHAR(100) NULL,
                status ENUM('pending','packed','shipped','delivered','returned') NOT NULL DEFAULT 'pending',
                shipped_at DATETIME NULL,
                delivered_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                KEY shipments_order_id_index (order_id),
                CONSTRAINT shipments_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS shipments');
    }
};
