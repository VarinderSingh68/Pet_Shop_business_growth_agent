<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class BlogComment extends Model
{
    protected static string $table = 'blog_comments';
    protected static bool $timestamps = false;
}
