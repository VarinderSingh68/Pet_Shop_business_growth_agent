<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE referrals (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                referrer_user_id INT UNSIGNED NOT NULL,
                code VARCHAR(20) NOT NULL,
                referred_user_id INT UNSIGNED NULL,
                status ENUM('pending','completed','rewarded','fraud_flagged') NOT NULL DEFAULT 'pending',
                reward_paise INT UNSIGNED NULL,
                fraud_reason VARCHAR(255) NULL,
                completed_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY referrals_code_unique (code),
                KEY referrals_referrer_id_index (referrer_user_id),
                KEY referrals_referred_id_index (referred_user_id),
                CONSTRAINT referrals_referrer_fk FOREIGN KEY (referrer_user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT referrals_referred_fk FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS referrals');
    }
};
