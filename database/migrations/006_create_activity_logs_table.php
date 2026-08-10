<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE activity_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INT NULL,
            action VARCHAR(150) NOT NULL,
            subject_type VARCHAR(100) NULL,
            subject_id INT NULL,
            description VARCHAR(500) NULL,
            properties JSON NULL,
            ip_address VARCHAR(45) NULL,
            created_at DATETIME NOT NULL,
            CONSTRAINT activity_logs_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX activity_logs_user_id_index ON activity_logs(user_id)');
        $pdo->exec('CREATE INDEX activity_logs_subject_index ON activity_logs(subject_type, subject_id)');
        $pdo->exec('CREATE INDEX activity_logs_created_at_index ON activity_logs(created_at)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS activity_logs');
    }
};
