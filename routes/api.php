<?php

declare(strict_types=1);

use App\Controllers\Api\OrdersController;
use App\Controllers\Api\PaymentController;
use App\Controllers\Api\ProductsController;
use App\Controllers\Api\SearchController;
use App\Core\Router;

/** @var Router $router */

$router->group(['prefix' => '/api/v1'], function (Router $router) {
    $router->get('/health', function () {
        \App\Core\Response::json(['status' => 'ok', 'time' => now()]);
    });

    $router->get('/search/autocomplete', [SearchController::class, 'autocomplete'], ['throttle:30,1']);

    $router->post('/payments/verify', [PaymentController::class, 'verify'], ['csrf', 'throttle:20,1']);
    $router->post('/payments/webhook/razorpay', [PaymentController::class, 'webhook'], ['throttle:60,1']);

    $router->get('/products', [ProductsController::class, 'index'], ['api_token']);
    $router->get('/orders', [OrdersController::class, 'index'], ['api_token']);
});
