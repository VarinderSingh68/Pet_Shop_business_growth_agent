<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE banners (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title VARCHAR(200) NOT NULL,
            body VARCHAR(500) NULL,
            link_url VARCHAR(255) NULL,
            link_label VARCHAR(100) NULL,
            display_type TEXT NOT NULL DEFAULT 'top_bar' CHECK (display_type IN ('top_bar','popup')),
            target_page TEXT NOT NULL DEFAULT 'all' CHECK (target_page IN ('all','home','shop')),
            starts_at DATETIME NULL,
            ends_at DATETIME NULL,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
            )
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS banners');
    }
};
