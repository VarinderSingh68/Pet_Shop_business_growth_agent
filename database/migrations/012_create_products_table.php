<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE products (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id INT UNSIGNED NOT NULL,
                brand_id INT UNSIGNED NULL,
                name VARCHAR(200) NOT NULL,
                slug VARCHAR(220) NOT NULL,
                short_description VARCHAR(300) NULL,
                description TEXT NULL,
                pet_type ENUM('dog','cat','bird','fish','small_pet','other') NOT NULL DEFAULT 'other',
                life_stage ENUM('puppy_kitten','adult','senior','all') NOT NULL DEFAULT 'all',
                status ENUM('draft','active','archived') NOT NULL DEFAULT 'active',
                feeding_grams_per_day INT UNSIGNED NULL COMMENT 'Average daily consumption, drives the feeds-for-N-days helper text and replenishment predictions',
                avg_rating DECIMAL(3,2) NOT NULL DEFAULT 0.00,
                review_count INT UNSIGNED NOT NULL DEFAULT 0,
                is_featured TINYINT(1) NOT NULL DEFAULT 0,
                meta_title VARCHAR(200) NULL,
                meta_description VARCHAR(300) NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                deleted_at DATETIME NULL,
                UNIQUE KEY products_slug_unique (slug),
                KEY products_category_id_index (category_id),
                KEY products_brand_id_index (brand_id),
                KEY products_pet_type_index (pet_type),
                KEY products_status_index (status),
                KEY products_deleted_at_index (deleted_at),
                FULLTEXT KEY products_search_fulltext (name, short_description, description),
                CONSTRAINT products_category_fk FOREIGN KEY (category_id) REFERENCES categories(id),
                CONSTRAINT products_brand_fk FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS products');
    }
};
