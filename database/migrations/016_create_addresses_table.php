<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE addresses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INT NOT NULL,
            label VARCHAR(50) NOT NULL DEFAULT 'Home',
            full_name VARCHAR(150) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            line1 VARCHAR(200) NOT NULL,
            line2 VARCHAR(200) NULL,
            city VARCHAR(100) NOT NULL,
            state VARCHAR(100) NOT NULL,
            postal_code VARCHAR(12) NOT NULL,
            country VARCHAR(2) NOT NULL DEFAULT 'IN',
            is_default INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            deleted_at DATETIME NULL,
            CONSTRAINT addresses_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE INDEX addresses_user_id_index ON addresses(user_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS addresses');
    }
};
