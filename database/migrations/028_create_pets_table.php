<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE pets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            species TEXT NOT NULL CHECK (species IN ('dog','cat','bird','fish','small_pet','other')),
            breed VARCHAR(100) NULL,
            sex TEXT NOT NULL DEFAULT 'unknown' CHECK (sex IN ('male','female','unknown')),
            birthday DATE NULL,
            weight_grams INT NULL,
            allergies VARCHAR(500) NULL,
            photo_path VARCHAR(255) NULL,
            notes VARCHAR(500) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            deleted_at DATETIME NULL,
            CONSTRAINT pets_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE INDEX pets_user_id_index ON pets(user_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS pets');
    }
};
