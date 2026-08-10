<?php

declare(strict_types=1);

/**
 * Corrects the referral model: a referral code belongs permanently to the
 * referrer (one code, reusable across many signups), not to a single
 * referral event. Moves the code onto users and turns `referrals` into a
 * pure per-event log (one row per person who actually signed up with it).
 */
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec('ALTER TABLE users ADD COLUMN referral_code VARCHAR(20) NULL AFTER phone');
        $pdo->exec('ALTER TABLE users ADD UNIQUE KEY users_referral_code_unique (referral_code)');

        $pdo->exec('ALTER TABLE referrals DROP FOREIGN KEY referrals_referred_fk');
        $pdo->exec('ALTER TABLE referrals MODIFY COLUMN referred_user_id INT UNSIGNED NOT NULL');
        $pdo->exec('ALTER TABLE referrals ADD CONSTRAINT referrals_referred_fk FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('ALTER TABLE referrals DROP FOREIGN KEY referrals_referred_fk');
        $pdo->exec('ALTER TABLE referrals MODIFY COLUMN referred_user_id INT UNSIGNED NULL');
        $pdo->exec('ALTER TABLE referrals ADD CONSTRAINT referrals_referred_fk FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE SET NULL');
        $pdo->exec('ALTER TABLE referrals ADD COLUMN code VARCHAR(20) NOT NULL AFTER referrer_user_id');
        $pdo->exec('ALTER TABLE referrals ADD UNIQUE KEY referrals_code_unique (code)');
        $pdo->exec('ALTER TABLE users DROP INDEX users_referral_code_unique');
        $pdo->exec('ALTER TABLE users DROP COLUMN referral_code');
    }
};
