<?php

declare(strict_types=1);

/**
 * Originally added Google sign-in support (nullable google_id, nullable
 * password_hash) via MySQL-only ALTER TABLE verbs that don't exist in
 * SQLite. Since this is a from-scratch SQLite schema with no history to
 * replay, those changes are baked directly into 004_create_users_table.php
 * instead. This migration is now a no-op, kept only so migration
 * numbering/tracking stays intact.
 */
return new class {
    public function up(PDO $pdo): void
    {
    }

    public function down(PDO $pdo): void
    {
    }
};
