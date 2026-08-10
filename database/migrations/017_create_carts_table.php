<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE carts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INT NULL,
            session_token VARCHAR(64) NULL,
            coupon_id INT NULL,
            abandoned_notified_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT carts_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE INDEX carts_user_id_index ON carts(user_id)');
        $pdo->exec('CREATE INDEX carts_session_token_index ON carts(session_token)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS carts');
    }
};
