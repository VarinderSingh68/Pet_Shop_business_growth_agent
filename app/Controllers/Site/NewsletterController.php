<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\NewsletterSubscriber;

final class NewsletterController extends Controller
{
    public function subscribe(Request $request): void
    {
        $email = (string) $request->input('email', '');

        $validator = Validator::make(['email' => $email], ['email' => 'required|email']);
        if ($validator->fails()) {
            flash('error', 'Please enter a valid email address.');
            back();
        }

        $existing = NewsletterSubscriber::findByEmail($email);
        if ($existing !== null) {
            if ($existing['status'] === 'unsubscribed') {
                NewsletterSubscriber::updateWhere((int) $existing['id'], ['status' => 'subscribed']);
            }
            flash('success', "You're on the list!");
            back();
        }

        NewsletterSubscriber::create([
            'email' => strtolower(trim($email)),
            'status' => 'subscribed',
            'unsubscribe_token' => bin2hex(random_bytes(20)),
        ]);

        flash('success', "You're on the list! Watch for offers and reminders.");
        back();
    }

    public function unsubscribe(Request $request, string $token): void
    {
        $subscriber = NewsletterSubscriber::findByToken($token);
        if ($subscriber !== null) {
            NewsletterSubscriber::updateWhere((int) $subscriber['id'], ['status' => 'unsubscribed']);
        }

        $this->view('site/newsletter-unsubscribed', ['title' => 'Unsubscribed']);
    }
}
