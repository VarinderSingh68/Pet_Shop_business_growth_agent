<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE wishlists (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT wishlists_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            CONSTRAINT wishlists_product_fk FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX wishlists_user_product_unique ON wishlists(user_id, product_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS wishlists');
    }
};
