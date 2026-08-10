<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\App;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Product;
use App\Services\Growth\ModerationService;

final class ReviewController extends Controller
{
    public function __construct(private readonly ModerationService $moderation = new ModerationService())
    {
    }

    public function store(Request $request): void
    {
        $user = App::auth()->user();
        $productId = (int) $request->input('product_id');
        $data = $request->only(['rating', 'title', 'body']);

        $validator = Validator::make($data, [
            'rating' => 'required|integer',
            'title' => 'max:150',
            'body' => 'max:2000',
        ]);

        $rating = (int) ($data['rating'] ?? 0);
        if ($validator->fails() || $rating < 1 || $rating > 5) {
            flash('error', 'Please choose a rating from 1 to 5 and try again.');
            back();
        }

        $verified = Database::instance()->selectOne(
            "SELECT 1 AS x FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             WHERE o.user_id = :uid AND oi.product_id = :pid AND o.status = 'delivered'
             LIMIT 1",
            ['uid' => $user['id'], 'pid' => $productId],
        ) !== null;

        $flaggedTerm = $this->moderation->matchedTerm(($data['title'] ?? '') . ' ' . ($data['body'] ?? ''));

        Database::instance()->insert('reviews', [
            'product_id' => $productId,
            'user_id' => $user['id'],
            'rating' => $rating,
            'title' => !empty($data['title']) ? $data['title'] : null,
            'body' => !empty($data['body']) ? $data['body'] : null,
            'is_verified_purchase' => $verified ? 1 : 0,
            'status' => $flaggedTerm !== null ? 'flagged' : 'pending',
            'flagged_reason' => $flaggedTerm !== null ? "Contains flagged term: \"{$flaggedTerm}\"" : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        flash('success', 'Thanks! Your review is awaiting a quick moderation check before it goes live.');
        back();
    }
}
