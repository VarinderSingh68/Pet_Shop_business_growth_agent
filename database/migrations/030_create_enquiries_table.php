<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE enquiries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INT NULL,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(191) NOT NULL,
            phone VARCHAR(20) NULL,
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            order_number VARCHAR(30) NULL,
            status TEXT NOT NULL DEFAULT 'open' CHECK (status IN ('open','in_progress','resolved')),
            staff_reply TEXT NULL,
            replied_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT enquiries_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX enquiries_user_id_index ON enquiries(user_id)');
        $pdo->exec('CREATE INDEX enquiries_status_index ON enquiries(status)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS enquiries');
    }
};
