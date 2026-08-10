<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            role_id INT NOT NULL,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(191) NOT NULL,
            phone VARCHAR(20) NULL,
            referral_code VARCHAR(20) NULL,
            google_id VARCHAR(50) NULL,
            password_hash VARCHAR(255) NULL,
            is_active INTEGER NOT NULL DEFAULT 1,
            email_verified_at DATETIME NULL,
            two_factor_secret VARCHAR(191) NULL,
            two_factor_enabled INTEGER NOT NULL DEFAULT 0,
            must_reset_password INTEGER NOT NULL DEFAULT 0,
            last_login_at DATETIME NULL,
            last_login_ip VARCHAR(45) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            deleted_at DATETIME NULL,
            CONSTRAINT users_role_fk FOREIGN KEY (role_id) REFERENCES roles(id)
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX users_email_unique ON users(email)');
        $pdo->exec('CREATE UNIQUE INDEX users_referral_code_unique ON users(referral_code)');
        $pdo->exec('CREATE UNIQUE INDEX users_google_id_unique ON users(google_id)');
        $pdo->exec('CREATE INDEX users_role_id_index ON users(role_id)');
        $pdo->exec('CREATE INDEX users_deleted_at_index ON users(deleted_at)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS users');
    }
};
