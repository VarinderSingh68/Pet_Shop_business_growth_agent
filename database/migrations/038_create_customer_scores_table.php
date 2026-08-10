<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE customer_scores (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INT NOT NULL,
            recency_days INT NULL,
            frequency_count INT NOT NULL DEFAULT 0,
            monetary_paise INT NOT NULL DEFAULT 0,
            recency_score INTEGER NOT NULL DEFAULT 1,
            frequency_score INTEGER NOT NULL DEFAULT 1,
            monetary_score INTEGER NOT NULL DEFAULT 1,
            rfm_total INTEGER NOT NULL DEFAULT 3,
            avg_order_interval_days INT NULL,
            predicted_next_order_date DATE NULL,
            churn_risk TEXT NOT NULL DEFAULT 'low' CHECK (churn_risk IN ('low','medium','high')),
            calculated_at DATETIME NOT NULL,
            CONSTRAINT customer_scores_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX customer_scores_user_unique ON customer_scores(user_id)');
        $pdo->exec('CREATE INDEX customer_scores_churn_risk_index ON customer_scores(churn_risk)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS customer_scores');
    }
};
