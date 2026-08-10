<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\Growth\ModerationService;

final class BlogController extends Controller
{
    public function __construct(private readonly ModerationService $moderation = new ModerationService())
    {
    }

    public function index(Request $request): void
    {
        $categorySlug = $request->query('category');
        $category = $categorySlug !== null ? BlogCategory::findBySlug((string) $categorySlug) : null;

        $this->view('site/blog/index', [
            'title' => 'Blog',
            'posts' => BlogPost::published($category['id'] ?? null),
            'categories' => Database::instance()->select('SELECT * FROM blog_categories ORDER BY name'),
            'activeCategory' => $category,
        ]);
    }

    public function show(Request $request, string $slug): void
    {
        $post = BlogPost::findBySlug($slug);
        if ($post === null) {
            abort(404);
        }

        $this->view('site/blog/show', [
            'title' => $post['title'],
            'description' => $post['excerpt'],
            'post' => $post,
            'comments' => BlogPost::approvedComments((int) $post['id']),
        ]);
    }

    public function storeComment(Request $request, string $id): void
    {
        $post = \App\Models\BlogPost::find((int) $id);
        if ($post === null) {
            abort(404);
        }

        $data = $request->only(['name', 'email', 'body']);
        $validator = Validator::make($data, [
            'name' => 'required|max:150',
            'email' => 'required|email',
            'body' => 'required|max:2000',
        ]);
        if ($validator->fails()) {
            flash('error', 'Please fill in your name, email, and comment.');
            back();
        }

        $flagged = $this->moderation->isAbusive((string) $data['body']);

        Database::instance()->insert('blog_comments', [
            'blog_post_id' => $id,
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'email' => $data['email'],
            'body' => $data['body'],
            'status' => $flagged ? 'flagged' : 'pending',
            'created_at' => now(),
        ]);

        flash('success', 'Thanks! Your comment is awaiting moderation.');
        back();
    }
}
