<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                role_id INT UNSIGNED NOT NULL,
                name VARCHAR(150) NOT NULL,
                email VARCHAR(191) NOT NULL,
                phone VARCHAR(20) NULL,
                password_hash VARCHAR(255) NOT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                email_verified_at DATETIME NULL,
                two_factor_secret VARCHAR(191) NULL,
                two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
                must_reset_password TINYINT(1) NOT NULL DEFAULT 0,
                last_login_at DATETIME NULL,
                last_login_ip VARCHAR(45) NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                deleted_at DATETIME NULL,
                UNIQUE KEY users_email_unique (email),
                KEY users_role_id_index (role_id),
                KEY users_deleted_at_index (deleted_at),
                CONSTRAINT users_role_fk FOREIGN KEY (role_id) REFERENCES roles(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS users');
    }
};
