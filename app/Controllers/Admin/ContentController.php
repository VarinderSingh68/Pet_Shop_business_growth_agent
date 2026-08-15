<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Testimonial;
use App\Services\MediaService;

final class ContentController extends Controller
{
    public function __construct(private readonly MediaService $media = new MediaService())
    {
    }

    // --- Blog -------------------------------------------------------------

    public function blogIndex(Request $request): void
    {
        $this->view('admin/content/blog/index', [
            'title' => 'Blog posts',
            'posts' => Database::instance()->select(
                'SELECT p.*, c.name AS category_name FROM blog_posts p LEFT JOIN blog_categories c ON c.id = p.blog_category_id ORDER BY p.created_at DESC',
            ),
        ]);
    }

    public function blogCreate(Request $request): void
    {
        $this->view('admin/content/blog/form', [
            'title' => 'New post',
            'post' => null,
            'categories' => BlogCategory::all('name ASC'),
        ]);
    }

    public function blogEdit(Request $request, string $id): void
    {
        $post = BlogPost::find((int) $id);
        if ($post === null) {
            abort(404);
        }

        $this->view('admin/content/blog/form', [
            'title' => 'Edit post',
            'post' => $post,
            'categories' => BlogCategory::all('name ASC'),
        ]);
    }

    public function blogStore(Request $request): void
    {
        $data = $this->validatedPostData($request);
        $data['slug'] = slugify($data['title']);
        $data['author_user_id'] = App::auth()->id();
        $data['cover_image_path'] = $this->uploadedCoverImage($request);

        $id = BlogPost::create($data);
        flash('success', 'Post created.');
        $this->redirect('/admin/content/blog/' . $id . '/edit');
    }

    public function blogUpdate(Request $request, string $id): void
    {
        $data = $this->validatedPostData($request);

        $newCover = $this->uploadedCoverImage($request);
        if ($newCover !== null) {
            $data['cover_image_path'] = $newCover;
        }

        BlogPost::updateWhere((int) $id, $data);
        flash('success', 'Post updated.');
        $this->redirect('/admin/content/blog/' . $id . '/edit');
    }

    private function uploadedCoverImage(Request $request): ?string
    {
        $file = $request->file('cover_image');
        if ($file === null) {
            return null;
        }

        try {
            return $this->media->storeImage($file, 'blog');
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            return null;
        }
    }

    public function blogDestroy(Request $request, string $id): void
    {
        Database::instance()->delete('blog_posts', 'id = :id', ['id' => $id]);
        flash('success', 'Post deleted.');
        $this->redirect('/admin/content/blog');
    }

    private function validatedPostData(Request $request): array
    {
        $data = $request->only(['title', 'blog_category_id', 'excerpt', 'body', 'status']);
        $validator = Validator::make($data, ['title' => 'required|max:200', 'body' => 'required']);
        if ($validator->fails()) {
            flash('error', 'Please enter a title and body.');
            back();
        }

        return [
            'title' => $data['title'],
            'blog_category_id' => !empty($data['blog_category_id']) ? (int) $data['blog_category_id'] : null,
            'excerpt' => !empty($data['excerpt']) ? $data['excerpt'] : null,
            'body' => $data['body'],
            'status' => $data['status'] === 'published' ? 'published' : 'draft',
            'published_at' => $data['status'] === 'published' ? now() : null,
        ];
    }

    public function blogCategories(Request $request): void
    {
        $this->view('admin/content/blog/categories', [
            'title' => 'Blog categories',
            'categories' => BlogCategory::all('name ASC'),
        ]);
    }

    public function storeBlogCategory(Request $request): void
    {
        $name = (string) $request->input('name', '');
        if ($name !== '') {
            BlogCategory::create(['name' => $name, 'slug' => slugify($name)]);
            flash('success', 'Category added.');
        }
        $this->redirect('/admin/content/blog/categories');
    }

    // --- Blog comments ----------------------------------------------------

    public function blogComments(Request $request): void
    {
        $status = (string) $request->query('status', 'pending');
        $where = in_array($status, ['pending', 'approved', 'flagged'], true) ? 'WHERE c.status = :status' : '';

        $comments = Database::instance()->select(
            "SELECT c.*, p.title AS post_title, p.slug AS post_slug
             FROM blog_comments c JOIN blog_posts p ON p.id = c.blog_post_id
             {$where}
             ORDER BY c.created_at DESC LIMIT 200",
            $where !== '' ? ['status' => $status] : [],
        );

        $counts = Database::instance()->select('SELECT status, COUNT(*) AS c FROM blog_comments GROUP BY status');

        $this->view('admin/content/blog/comments', [
            'title' => 'Blog comments',
            'comments' => $comments,
            'status' => $status,
            'countsByStatus' => array_column($counts, 'c', 'status'),
        ]);
    }

    public function updateBlogCommentStatus(Request $request, string $id): void
    {
        $status = (string) $request->input('status', '');
        if (!in_array($status, ['pending', 'approved', 'flagged'], true)) {
            back();
        }

        Database::instance()->update('blog_comments', ['status' => $status], 'id = :id', ['id' => $id]);
        flash('success', 'Comment updated.');
        back();
    }

    public function destroyBlogComment(Request $request, string $id): void
    {
        Database::instance()->delete('blog_comments', 'id = :id', ['id' => $id]);
        flash('success', 'Comment deleted.');
        back();
    }

    // --- Pages --------------------------------------------------------------

    public function pages(Request $request): void
    {
        $this->view('admin/content/pages/index', ['title' => 'Pages', 'pages' => Page::all('title ASC')]);
    }

    public function pageCreate(Request $request): void
    {
        $this->view('admin/content/pages/form', ['title' => 'New page', 'page' => null]);
    }

    public function pageEdit(Request $request, string $id): void
    {
        $page = Page::find((int) $id);
        if ($page === null) {
            abort(404);
        }
        $this->view('admin/content/pages/form', ['title' => 'Edit page', 'page' => $page]);
    }

    public function pageStore(Request $request): void
    {
        $data = $this->validatedPageData($request);
        $data['slug'] = slugify($data['title']);
        $id = Page::create($data);
        flash('success', 'Page created.');
        $this->redirect('/admin/content/pages/' . $id . '/edit');
    }

    public function pageUpdate(Request $request, string $id): void
    {
        Page::updateWhere((int) $id, $this->validatedPageData($request));
        flash('success', 'Page updated.');
        $this->redirect('/admin/content/pages/' . $id . '/edit');
    }

    public function pageDestroy(Request $request, string $id): void
    {
        Database::instance()->delete('pages', 'id = :id', ['id' => $id]);
        flash('success', 'Page deleted.');
        $this->redirect('/admin/content/pages');
    }

    private function validatedPageData(Request $request): array
    {
        $data = $request->only(['title', 'body', 'meta_title', 'meta_description', 'is_published']);
        $validator = Validator::make($data, ['title' => 'required|max:150', 'body' => 'required']);
        if ($validator->fails()) {
            flash('error', 'Please enter a title and body.');
            back();
        }

        return [
            'title' => $data['title'],
            'body' => $data['body'],
            'meta_title' => !empty($data['meta_title']) ? $data['meta_title'] : null,
            'meta_description' => !empty($data['meta_description']) ? $data['meta_description'] : null,
            'is_published' => !empty($data['is_published']) ? 1 : 0,
        ];
    }

    // --- FAQs -----------------------------------------------------------

    public function faqs(Request $request): void
    {
        $this->view('admin/content/faqs', ['title' => 'FAQs', 'faqs' => Faq::all('sort_order ASC')]);
    }

    public function storeFaq(Request $request): void
    {
        $data = $request->only(['question', 'answer', 'sort_order']);
        if (!empty($data['question']) && !empty($data['answer'])) {
            Faq::create(['question' => $data['question'], 'answer' => $data['answer'], 'sort_order' => (int) ($data['sort_order'] ?? 0), 'is_published' => 1]);
            flash('success', 'FAQ added.');
        }
        $this->redirect('/admin/content/faqs');
    }

    public function updateFaq(Request $request, string $id): void
    {
        $data = $request->only(['question', 'answer', 'sort_order', 'is_published']);
        Faq::updateWhere((int) $id, [
            'question' => $data['question'],
            'answer' => $data['answer'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_published' => !empty($data['is_published']) ? 1 : 0,
        ]);
        $this->redirect('/admin/content/faqs');
    }

    public function destroyFaq(Request $request, string $id): void
    {
        Database::instance()->delete('faqs', 'id = :id', ['id' => $id]);
        $this->redirect('/admin/content/faqs');
    }

    // --- Testimonials ---------------------------------------------------

    public function testimonials(Request $request): void
    {
        $this->view('admin/content/testimonials', ['title' => 'Testimonials', 'testimonials' => Testimonial::all('sort_order ASC')]);
    }

    public function storeTestimonial(Request $request): void
    {
        $data = $request->only(['customer_name', 'pet_description', 'quote', 'rating']);
        if (!empty($data['customer_name']) && !empty($data['quote'])) {
            Testimonial::create([
                'customer_name' => $data['customer_name'],
                'pet_description' => !empty($data['pet_description']) ? $data['pet_description'] : null,
                'quote' => $data['quote'],
                'rating' => max(1, min(5, (int) ($data['rating'] ?? 5))),
                'is_published' => 1,
            ]);
            flash('success', 'Testimonial added.');
        }
        $this->redirect('/admin/content/testimonials');
    }

    public function toggleTestimonial(Request $request, string $id): void
    {
        $t = Testimonial::find((int) $id);
        if ($t !== null) {
            Testimonial::updateWhere((int) $id, ['is_published' => $t['is_published'] ? 0 : 1]);
        }
        $this->redirect('/admin/content/testimonials');
    }

    public function destroyTestimonial(Request $request, string $id): void
    {
        Database::instance()->delete('testimonials', 'id = :id', ['id' => $id]);
        $this->redirect('/admin/content/testimonials');
    }

    // --- Banners ----------------------------------------------------------

    public function banners(Request $request): void
    {
        $this->view('admin/content/banners', ['title' => 'Banners', 'banners' => Database::instance()->select('SELECT * FROM banners ORDER BY id DESC')]);
    }

    public function storeBanner(Request $request): void
    {
        $data = $request->only(['title', 'body', 'link_url', 'link_label', 'display_type', 'target_page']);
        if (empty($data['title'])) {
            $this->redirect('/admin/content/banners');
        }

        Database::instance()->insert('banners', [
            'title' => $data['title'],
            'body' => !empty($data['body']) ? $data['body'] : null,
            'link_url' => !empty($data['link_url']) ? $data['link_url'] : null,
            'link_label' => !empty($data['link_label']) ? $data['link_label'] : null,
            'display_type' => in_array($data['display_type'] ?? '', ['top_bar', 'popup'], true) ? $data['display_type'] : 'top_bar',
            'target_page' => in_array($data['target_page'] ?? '', ['all', 'home', 'shop'], true) ? $data['target_page'] : 'all',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        flash('success', 'Banner created.');
        $this->redirect('/admin/content/banners');
    }

    public function toggleBanner(Request $request, string $id): void
    {
        $b = Database::instance()->selectOne('SELECT is_active FROM banners WHERE id = :id', ['id' => $id]);
        if ($b !== null) {
            Database::instance()->update('banners', ['is_active' => $b['is_active'] ? 0 : 1], 'id = :id', ['id' => $id]);
        }
        $this->redirect('/admin/content/banners');
    }

    public function destroyBanner(Request $request, string $id): void
    {
        Database::instance()->delete('banners', 'id = :id', ['id' => $id]);
        $this->redirect('/admin/content/banners');
    }
}
