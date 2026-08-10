<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE blog_categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(120) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX blog_categories_slug_unique ON blog_categories(slug)');
        $pdo->exec(<<<SQL
            CREATE TABLE blog_posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            blog_category_id INT NULL,
            author_user_id INT NULL,
            title VARCHAR(200) NOT NULL,
            slug VARCHAR(220) NOT NULL,
            excerpt VARCHAR(300) NULL,
            body TEXT NOT NULL,
            cover_image_path VARCHAR(255) NULL,
            status TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published')),
            published_at DATETIME NULL,
            meta_title VARCHAR(200) NULL,
            meta_description VARCHAR(300) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT blog_posts_category_fk FOREIGN KEY (blog_category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
            CONSTRAINT blog_posts_author_fk FOREIGN KEY (author_user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE UNIQUE INDEX blog_posts_slug_unique ON blog_posts(slug)');
        $pdo->exec('CREATE INDEX blog_posts_category_id_index ON blog_posts(blog_category_id)');
        $pdo->exec('CREATE INDEX blog_posts_status_index ON blog_posts(status)');
        $pdo->exec(<<<SQL
            CREATE TABLE blog_comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            blog_post_id INT NOT NULL,
            user_id INT NULL,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(191) NOT NULL,
            body VARCHAR(2000) NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','approved','flagged')),
            created_at DATETIME NOT NULL,
            CONSTRAINT blog_comments_post_fk FOREIGN KEY (blog_post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
            CONSTRAINT blog_comments_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);
        $pdo->exec('CREATE INDEX blog_comments_post_id_index ON blog_comments(blog_post_id)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS blog_comments');
        $pdo->exec('DROP TABLE IF EXISTS blog_posts');
        $pdo->exec('DROP TABLE IF EXISTS blog_categories');
    }
};
