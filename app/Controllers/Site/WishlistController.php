<?php

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\App;
use App\Core\Controller;
use App\Core\Request;
use App\Models\Wishlist;

final class WishlistController extends Controller
{
    public function toggle(Request $request): void
    {
        $productId = (int) $request->input('product_id');
        $added = Wishlist::toggle((int) App::auth()->id(), $productId);

        if ($request->wantsJson()) {
            $this->json(['added' => $added]);
        }

        flash('success', $added ? 'Added to your wishlist.' : 'Removed from your wishlist.');
        back();
    }
}
