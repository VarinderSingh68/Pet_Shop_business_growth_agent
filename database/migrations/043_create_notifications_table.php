<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INT NULL,
            type VARCHAR(50) NOT NULL,
            channel TEXT NOT NULL DEFAULT 'email' CHECK (channel IN ('email','sms','whatsapp','banner','system')),
            subject VARCHAR(200) NULL,
            body TEXT NULL,
            status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','sent','failed')),
            related_type VARCHAR(50) NULL,
            related_id INT NULL,
            sent_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT notifications_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        SQL);
        $pdo->exec('CREATE INDEX notifications_user_id_index ON notifications(user_id)');
        $pdo->exec('CREATE INDEX notifications_type_index ON notifications(type)');
        $pdo->exec('CREATE INDEX notifications_related_index ON notifications(related_type, related_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS notifications');
    }
};
