<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE webhook_deliveries (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                source VARCHAR(50) NOT NULL DEFAULT 'razorpay',
                event_type VARCHAR(100) NULL,
                signature_valid TINYINT(1) NOT NULL DEFAULT 0,
                payload LONGTEXT NOT NULL,
                headers JSON NULL,
                outcome ENUM('success','failed') NOT NULL,
                error VARCHAR(500) NULL,
                created_at DATETIME NOT NULL,
                KEY webhook_deliveries_source_index (source),
                KEY webhook_deliveries_created_at_index (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS webhook_deliveries');
    }
};
