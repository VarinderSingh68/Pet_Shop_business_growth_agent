<?php

declare(strict_types=1);

/**
 * Adds Google sign-in support: a nullable google_id (accounts created via
 * Google never set one otherwise) and makes password_hash nullable, since a
 * Google-only account has no password of its own until the customer sets one.
 */
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec('ALTER TABLE users ADD COLUMN google_id VARCHAR(50) NULL AFTER referral_code');
        $pdo->exec('ALTER TABLE users ADD UNIQUE KEY users_google_id_unique (google_id)');
        $pdo->exec('ALTER TABLE users MODIFY COLUMN password_hash VARCHAR(255) NULL');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('ALTER TABLE users MODIFY COLUMN password_hash VARCHAR(255) NOT NULL');
        $pdo->exec('ALTER TABLE users DROP INDEX users_google_id_unique');
        $pdo->exec('ALTER TABLE users DROP COLUMN google_id');
    }
};
