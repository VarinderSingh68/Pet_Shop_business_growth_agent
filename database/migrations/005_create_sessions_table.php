<?php

declare(strict_types=1);

/**
 * Tracks logged-in sessions per user (separate from PHP's own session store)
 * so the admin panel can list active sessions and force a remote logout.
 */
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE sessions (
                id VARCHAR(191) NOT NULL PRIMARY KEY,
                user_id INT UNSIGNED NULL,
                ip_address VARCHAR(45) NULL,
                user_agent VARCHAR(255) NULL,
                last_activity DATETIME NOT NULL,
                created_at DATETIME NOT NULL,
                KEY sessions_user_id_index (user_id),
                CONSTRAINT sessions_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS sessions');
    }
};
