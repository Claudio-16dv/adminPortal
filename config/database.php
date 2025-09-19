<?php

use Dotenv\Dotenv;

if (!class_exists(Dotenv::class)) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$databaseConfig = [
    'DB_CONNECTION' => $_ENV['DB_CONNECTION'] ?? 'mysql',
    'DB_HOST'       => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'DB_PORT'       => $_ENV['DB_PORT'] ?? '3306',
    'DB_NAME'       => $_ENV['DB_NAME'] ?? '',
    'DB_USER'       => $_ENV['DB_USER'] ?? '',
    'DB_PASS'       => $_ENV['DB_PASS'] ?? '',
];