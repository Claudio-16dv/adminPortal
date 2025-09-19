<?php

use App\Core\Kernel;
use App\Core\Middleware\ErrorHandlerMiddleware;
use App\Core\Middleware\JsonContentTypeMiddleware;
use App\Core\Middleware\CorsMiddleware;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$psr17Factory = new Psr17Factory();
$creator = new ServerRequestCreator(
    $psr17Factory,
    $psr17Factory,
    $psr17Factory,
    $psr17Factory
);

$request = $creator->fromGlobals();

$middlewares = [
    new CorsMiddleware(),
    new JsonContentTypeMiddleware(),
    new ErrorHandlerMiddleware(),
];

$kernel = new Kernel($middlewares, $request);

$kernel->run();
