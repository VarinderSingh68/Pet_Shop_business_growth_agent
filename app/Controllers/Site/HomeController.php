<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Testimonial;

final class HomeController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('site/home', [
            'title' => 'Food, gear, and care for the animal you\'re raising',
            'bannerPage' => 'home',
            'testimonials' => Testimonial::published(),
        ]);
    }
}
