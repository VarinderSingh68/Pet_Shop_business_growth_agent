<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE newsletter_subscribers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email VARCHAR(191) NOT NULL,
            status TEXT NOT NULL DEFAULT 'subscribed' CHECK (status IN ('subscribed','unsubscribed')),
            unsubscribe_token VARCHAR(64) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX newsletter_subscribers_email_unique ON newsletter_subscribers(email)');
        $pdo->exec('CREATE UNIQUE INDEX newsletter_subscribers_token_unique ON newsletter_subscribers(unsubscribe_token)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS newsletter_subscribers');
    }
};
