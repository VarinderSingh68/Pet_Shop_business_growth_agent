<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE reviews (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INT NOT NULL,
            user_id INT NULL,
            order_item_id INT NULL,
            rating INTEGER NOT NULL,
            title VARCHAR(150) NULL,
            body TEXT NULL,
            photo_paths JSON NULL,
            is_verified_purchase INTEGER NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','approved','flagged')),
            flagged_reason VARCHAR(255) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT reviews_product_fk FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            CONSTRAINT reviews_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX reviews_product_id_index ON reviews(product_id)');
        $pdo->exec('CREATE INDEX reviews_user_id_index ON reviews(user_id)');
        $pdo->exec('CREATE INDEX reviews_status_index ON reviews(status)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS reviews');
    }
};
