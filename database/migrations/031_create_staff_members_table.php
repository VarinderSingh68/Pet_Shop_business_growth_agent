<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE staff_members (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INT NULL,
            name VARCHAR(150) NOT NULL,
            title VARCHAR(100) NULL,
            bio VARCHAR(500) NULL,
            photo_path VARCHAR(255) NULL,
            working_hours JSON NULL,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT staff_members_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX staff_members_user_id_index ON staff_members(user_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS staff_members');
    }
};
