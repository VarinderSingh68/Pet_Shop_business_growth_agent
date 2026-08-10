<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INT NOT NULL,
            brand_id INT NULL,
            name VARCHAR(200) NOT NULL,
            slug VARCHAR(220) NOT NULL,
            short_description VARCHAR(300) NULL,
            description TEXT NULL,
            pet_type TEXT NOT NULL DEFAULT 'other' CHECK (pet_type IN ('dog','cat','bird','fish','small_pet','other')),
            life_stage TEXT NOT NULL DEFAULT 'all' CHECK (life_stage IN ('puppy_kitten','adult','senior','all')),
            status TEXT NOT NULL DEFAULT 'active' CHECK (status IN ('draft','active','archived')),
            feeding_grams_per_day INT NULL,
            avg_rating DECIMAL(3,2) NOT NULL DEFAULT 0.00,
            review_count INT NOT NULL DEFAULT 0,
            is_featured INTEGER NOT NULL DEFAULT 0,
            meta_title VARCHAR(200) NULL,
            meta_description VARCHAR(300) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            deleted_at DATETIME NULL,
            CONSTRAINT products_category_fk FOREIGN KEY (category_id) REFERENCES categories(id),
            CONSTRAINT products_brand_fk FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX products_slug_unique ON products(slug)');
        $pdo->exec('CREATE INDEX products_category_id_index ON products(category_id)');
        $pdo->exec('CREATE INDEX products_brand_id_index ON products(brand_id)');
        $pdo->exec('CREATE INDEX products_pet_type_index ON products(pet_type)');
        $pdo->exec('CREATE INDEX products_status_index ON products(status)');
        $pdo->exec('CREATE INDEX products_deleted_at_index ON products(deleted_at)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS products');
    }
};
