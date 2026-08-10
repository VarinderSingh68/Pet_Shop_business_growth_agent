<?php

declare(strict_types=1);

/**
 * Originally moved the referral code from `referrals` onto `users` and
 * tightened `referrals.referred_user_id` to NOT NULL — via MySQL-only
 * ALTER TABLE verbs (MODIFY COLUMN, DROP FOREIGN KEY, ADD COLUMN ... AFTER)
 * that don't exist in SQLite. Since this is a from-scratch SQLite schema
 * with no history to replay, those changes are baked directly into
 * 004_create_users_table.php and 042_create_referrals_table.php instead.
 * This migration is now a no-op, kept only so migration numbering/tracking
 * stays intact.
 */
return new class {
    public function up(PDO $pdo): void
    {
    }

    public function down(PDO $pdo): void
    {
    }
};
