<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE pets (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                name VARCHAR(100) NOT NULL,
                species ENUM('dog','cat','bird','fish','small_pet','other') NOT NULL,
                breed VARCHAR(100) NULL,
                sex ENUM('male','female','unknown') NOT NULL DEFAULT 'unknown',
                birthday DATE NULL,
                weight_grams INT UNSIGNED NULL,
                allergies VARCHAR(500) NULL,
                photo_path VARCHAR(255) NULL,
                notes VARCHAR(500) NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                deleted_at DATETIME NULL,
                KEY pets_user_id_index (user_id),
                CONSTRAINT pets_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS pets');
    }
};
