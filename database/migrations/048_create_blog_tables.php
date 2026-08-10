<?php

declare(strict_types=1);

return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<SQL
            CREATE TABLE blog_categories (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(120) NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY blog_categories_slug_unique (slug)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $pdo->exec(<<<SQL
            CREATE TABLE blog_posts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                blog_category_id INT UNSIGNED NULL,
                author_user_id INT UNSIGNED NULL,
                title VARCHAR(200) NOT NULL,
                slug VARCHAR(220) NOT NULL,
                excerpt VARCHAR(300) NULL,
                body LONGTEXT NOT NULL,
                cover_image_path VARCHAR(255) NULL,
                status ENUM('draft','published') NOT NULL DEFAULT 'draft',
                published_at DATETIME NULL,
                meta_title VARCHAR(200) NULL,
                meta_description VARCHAR(300) NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY blog_posts_slug_unique (slug),
                KEY blog_posts_category_id_index (blog_category_id),
                KEY blog_posts_status_index (status),
                CONSTRAINT blog_posts_category_fk FOREIGN KEY (blog_category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
                CONSTRAINT blog_posts_author_fk FOREIGN KEY (author_user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);

        $pdo->exec(<<<SQL
            CREATE TABLE blog_comments (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                blog_post_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NULL,
                name VARCHAR(150) NOT NULL,
                email VARCHAR(191) NOT NULL,
                body VARCHAR(2000) NOT NULL,
                status ENUM('pending','approved','flagged') NOT NULL DEFAULT 'pending',
                created_at DATETIME NOT NULL,
                KEY blog_comments_post_id_index (blog_post_id),
                CONSTRAINT blog_comments_post_fk FOREIGN KEY (blog_post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
                CONSTRAINT blog_comments_user_fk FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        SQL);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS blog_comments');
        $pdo->exec('DROP TABLE IF EXISTS blog_posts');
        $pdo->exec('DROP TABLE IF EXISTS blog_categories');
    }
};
