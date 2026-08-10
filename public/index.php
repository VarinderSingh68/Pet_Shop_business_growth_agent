<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

App::bootstrap();

$router = new Router();

require dirname(__DIR__) . '/routes/web.php';
require dirname(__DIR__) . '/routes/admin.php';
require dirname(__DIR__) . '/routes/api.php';

$router->dispatch(App::request());
