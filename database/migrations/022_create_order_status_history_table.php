<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE order_status_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INT NOT NULL,
            status VARCHAR(30) NOT NULL,
            note VARCHAR(255) NULL,
            changed_by_user_id INT NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT order_status_history_order_fk FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            CONSTRAINT order_status_history_user_fk FOREIGN KEY (changed_by_user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX order_status_history_order_id_index ON order_status_history(order_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS order_status_history');
    }
};
