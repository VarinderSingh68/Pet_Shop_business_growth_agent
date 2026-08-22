<?php

declare(strict_types=1);

use App\Controllers\Api\CronController;
use App\Controllers\Api\DeliveryController;
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

    $router->get('/cron/run', [CronController::class, 'run'], ['throttle:10,1']);

    $router->get('/search/autocomplete', [SearchController::class, 'autocomplete'], ['throttle:30,1']);

    $router->post('/payments/verify', [PaymentController::class, 'verify'], ['csrf', 'throttle:20,1']);
    $router->post('/payments/webhook/razorpay', [PaymentController::class, 'webhook'], ['throttle:60,1']);

    $router->get('/products', [ProductsController::class, 'index'], ['api_token']);
    $router->get('/orders', [OrdersController::class, 'index'], ['api_token']);

    // Delivery-partner (rider) Android app — see API.md and DELIVERY_APP.md.
    $router->group(['prefix' => '/delivery'], function (Router $router) {
        $router->post('/login', [DeliveryController::class, 'login'], ['throttle:10,1']);
        $router->post('/register', [DeliveryController::class, 'register'], ['throttle:10,1']);
        $router->get('/track/{orderNumber}', [DeliveryController::class, 'track'], ['throttle:30,1']);

        $router->get('/orders', [DeliveryController::class, 'orders'], ['delivery_token']);
        $router->get('/orders/{id}', [DeliveryController::class, 'show'], ['delivery_token']);
        $router->post('/orders/{id}/status', [DeliveryController::class, 'updateStatus'], ['delivery_token']);
        $router->post('/location', [DeliveryController::class, 'location'], ['delivery_token']);
    });
});
