<?php

declare(strict_types=1);

use App\Controllers\Site\AccountController;
use App\Controllers\Site\AuthController;
use App\Controllers\Site\BlogController;
use App\Controllers\Site\CampaignTrackingController;
use App\Controllers\Site\CartController;
use App\Controllers\Site\CatalogueController;
use App\Controllers\Site\CheckoutController;
use App\Controllers\Site\ContactController;
use App\Controllers\Site\HomeController;
use App\Controllers\Site\MediaController;
use App\Controllers\Site\NewsletterController;
use App\Controllers\Site\OrderController;
use App\Controllers\Site\PageController;
use App\Controllers\Site\ReviewController;
use App\Controllers\Site\ServiceController;
use App\Controllers\Site\WishlistController;
use App\Core\Router;

/** @var Router $router */

// Media is served even in maintenance mode (admin previews, existing pages
// with embedded images shouldn't break), so it sits outside the group below.
$router->get('/media/{subdir}/{filename}', [MediaController::class, 'show']);

$router->group(['middleware' => ['maintenance']], function (Router $router) {
    $router->get('/', [HomeController::class, 'index']);

    $router->get('/shop', [CatalogueController::class, 'index']);
    $router->get('/shop/{slug}', [CatalogueController::class, 'show']);

    $router->get('/cart', [CartController::class, 'index']);
    $router->post('/cart/add', [CartController::class, 'add'], ['csrf', 'throttle:30,1']);
    $router->post('/cart/update', [CartController::class, 'update'], ['csrf']);
    $router->post('/cart/remove', [CartController::class, 'remove'], ['csrf']);
    $router->post('/cart/coupon', [CartController::class, 'applyCoupon'], ['csrf', 'throttle:10,1']);
    $router->post('/cart/coupon/remove', [CartController::class, 'removeCoupon'], ['csrf']);
    $router->get('/reorder/{variantId}', [CartController::class, 'quickReorder'], ['throttle:30,1']);

    $router->get('/c/{token}', [CampaignTrackingController::class, 'click'], ['throttle:60,1']);
    $router->get('/o/{token}', [CampaignTrackingController::class, 'pixel'], ['throttle:120,1']);

    $router->get('/checkout', [CheckoutController::class, 'show']);
    $router->post('/checkout', [CheckoutController::class, 'place'], ['csrf', 'throttle:10,1']);
    $router->get('/checkout/pay/{orderNumber}', [CheckoutController::class, 'pay']);
    $router->get('/checkout/confirmation/{orderNumber}', [CheckoutController::class, 'confirmation']);

    $router->get('/orders/track/{orderNumber}', [OrderController::class, 'track'], ['throttle:20,1']);
    $router->get('/account/orders/{id}/invoice', [OrderController::class, 'invoice']);

    $router->post('/wishlist/toggle', [WishlistController::class, 'toggle'], ['auth', 'csrf']);
    $router->post('/reviews', [ReviewController::class, 'store'], ['auth', 'csrf', 'throttle:10,1']);

    $router->get('/services', [ServiceController::class, 'index']);
    $router->get('/services/{slug}', [ServiceController::class, 'show']);
    $router->post('/services/book', [ServiceController::class, 'book'], ['csrf', 'throttle:10,1']);
    $router->get('/services/booking/{bookingNumber}', [ServiceController::class, 'confirmation']);
    $router->post('/account/appointments/{id}/cancel', [ServiceController::class, 'cancel'], ['auth', 'csrf']);

    $router->get('/blog', [BlogController::class, 'index']);
    $router->get('/blog/{slug}', [BlogController::class, 'show']);
    $router->post('/blog/{id}/comments', [BlogController::class, 'storeComment'], ['csrf', 'throttle:10,1']);

    $router->get('/faq', [PageController::class, 'faqs']);
    $router->get('/contact', [ContactController::class, 'show']);
    $router->post('/contact', [ContactController::class, 'store'], ['csrf', 'throttle:5,1']);

    $router->post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'], ['csrf', 'throttle:10,1']);
    $router->get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe']);

    $router->get('/legal/{slug}', [PageController::class, 'show']);

    $router->group(['prefix' => '/account'], function (Router $router) {
        $router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
        $router->post('/login', [AuthController::class, 'login'], ['guest', 'csrf', 'throttle:10,1']);
        $router->get('/register', [AuthController::class, 'showRegister'], ['guest']);
        $router->post('/register', [AuthController::class, 'register'], ['guest', 'csrf', 'throttle:10,1']);
        $router->post('/logout', [AuthController::class, 'logout'], ['auth', 'csrf']);
        $router->get('/login/google', [AuthController::class, 'googleRedirect'], ['guest', 'throttle:10,1']);
        $router->get('/login/google/callback', [AuthController::class, 'googleCallback'], ['guest', 'throttle:10,1']);

        $router->get('/', [AccountController::class, 'index'], ['auth']);
        $router->get('/orders', [AccountController::class, 'orders'], ['auth']);
        $router->get('/pets', [AccountController::class, 'pets'], ['auth']);
        $router->post('/pets', [AccountController::class, 'storePet'], ['auth', 'csrf']);
        $router->post('/pets/{id}/delete', [AccountController::class, 'destroyPet'], ['auth', 'csrf']);
        $router->get('/addresses', [AccountController::class, 'addresses'], ['auth']);
        $router->post('/addresses', [AccountController::class, 'storeAddress'], ['auth', 'csrf']);
        $router->post('/addresses/{id}/delete', [AccountController::class, 'destroyAddress'], ['auth', 'csrf']);
        $router->get('/wishlist', [AccountController::class, 'wishlist'], ['auth']);
        $router->get('/support', [AccountController::class, 'support'], ['auth']);
        $router->post('/support', [AccountController::class, 'storeSupport'], ['auth', 'csrf', 'throttle:10,1']);
        $router->get('/appointments', [AccountController::class, 'appointments'], ['auth']);
        $router->get('/subscriptions', [AccountController::class, 'subscriptions'], ['auth']);
        $router->post('/subscriptions', [AccountController::class, 'storeSubscription'], ['auth', 'csrf']);
        $router->post('/subscriptions/{id}/pause', [AccountController::class, 'pauseSubscription'], ['auth', 'csrf']);
        $router->post('/subscriptions/{id}/resume', [AccountController::class, 'resumeSubscription'], ['auth', 'csrf']);
        $router->post('/subscriptions/{id}/skip', [AccountController::class, 'skipSubscription'], ['auth', 'csrf']);
        $router->post('/subscriptions/{id}/cancel', [AccountController::class, 'cancelSubscription'], ['auth', 'csrf']);
        $router->get('/rewards', [AccountController::class, 'rewards'], ['auth']);
    });
});
