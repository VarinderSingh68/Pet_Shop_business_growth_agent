<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Setting;

final class PageController extends Controller
{
    public function show(Request $request, string $slug): void
    {
        $page = Page::findBySlug($slug);
        if ($page === null) {
            abort(404);
        }

        $body = str_replace(
            ['{{store_name}}', '{{store_address}}', '{{store_email}}', '{{store_phone}}'],
            [
                Setting::get('store_name', 'Happy Tails Pet Store'),
                Setting::get('store_address', 'Bengaluru, Karnataka, India'),
                Setting::get('store_email', 'hello@happytails.test'),
                Setting::get('store_phone', '+91 00000 00000'),
            ],
            $page['body'],
        );

        $this->view('site/pages/show', [
            'title' => $page['title'],
            'description' => $page['meta_description'],
            'page' => $page,
            'body' => $body,
        ]);
    }

    public function faqs(Request $request): void
    {
        $this->view('site/pages/faqs', [
            'title' => 'Frequently asked questions',
            'faqs' => Faq::published(),
        ]);
    }
}
