<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE customer_scores (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                recency_days INT UNSIGNED NULL COMMENT 'Days since last order',
                frequency_count INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Orders in the last 365 days',
                monetary_paise INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total spend in the last 365 days',
                recency_score TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1-5, 5 = most recent',
                frequency_score TINYINT UNSIGNED NOT NULL DEFAULT 1,
                monetary_score TINYINT UNSIGNED NOT NULL DEFAULT 1,
                rfm_total TINYINT UNSIGNED NOT NULL DEFAULT 3,
                avg_order_interval_days INT UNSIGNED NULL COMMENT 'Mean days between this customer''s own orders',
                predicted_next_order_date DATE NULL,
                churn_risk ENUM('low','medium','high') NOT NULL DEFAULT 'low',
                calculated_at DATETIME NOT NULL,
                UNIQUE KEY customer_scores_user_unique (user_id),
                KEY customer_scores_churn_risk_index (churn_risk),
                CONSTRAINT customer_scores_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS customer_scores');
    }
};
