<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE referrals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            referrer_user_id INT NOT NULL,
            code VARCHAR(20) NOT NULL,
            referred_user_id INT NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','completed','rewarded','fraud_flagged')),
            reward_paise INT NULL,
            fraud_reason VARCHAR(255) NULL,
            completed_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT referrals_referrer_fk FOREIGN KEY (referrer_user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT referrals_referred_fk FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX referrals_code_unique ON referrals(code)');
        $pdo->exec('CREATE INDEX referrals_referrer_id_index ON referrals(referrer_user_id)');
        $pdo->exec('CREATE INDEX referrals_referred_id_index ON referrals(referred_user_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS referrals');
    }
};
