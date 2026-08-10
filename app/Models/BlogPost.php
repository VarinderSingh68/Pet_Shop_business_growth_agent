<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class BlogPost extends Model
{
    protected static string $table = 'blog_posts';

    public static function findBySlug(string $slug): ?array
    {
        return static::db()->selectOne(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name
             FROM blog_posts p
             LEFT JOIN blog_categories c ON c.id = p.blog_category_id
             LEFT JOIN users u ON u.id = p.author_user_id
             WHERE p.slug = :slug AND p.status = "published"',
            ['slug' => $slug],
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function published(?int $categoryId = null, int $limit = 20, int $offset = 0): array
    {
        $where = "p.status = 'published'";
        $bindings = [];
        if ($categoryId !== null) {
            $where .= ' AND p.blog_category_id = :cat';
            $bindings['cat'] = $categoryId;
        }

        return static::db()->select(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM blog_posts p LEFT JOIN blog_categories c ON c.id = p.blog_category_id
             WHERE {$where}
             ORDER BY p.published_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $bindings,
        );
    }

    public static function approvedComments(int $postId): array
    {
        return static::db()->select(
            "SELECT * FROM blog_comments WHERE blog_post_id = :id AND status = 'approved' ORDER BY created_at ASC",
            ['id' => $postId],
        );
    }
}
