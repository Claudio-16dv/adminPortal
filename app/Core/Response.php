<?php

namespace App\Core;

use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Response
{
    public static function json(array $data = [], int $status = 200): ResponseInterface
    {
        return new Psr7Response(
            $status,
            ['Content-Type' => 'application/json'],
            json_encode($data, self::jsonFlags())
        );
    }

    public static function created(array $data = []): ResponseInterface
    {
        return self::json($data, 201);
    }

    public static function error(string $message, int $status = 400): ResponseInterface
    {
        return self::json(['error' => $message], $status);
    }

    public static function noContent(): ResponseInterface
    {
        return new Psr7Response(204);
    }

    public static function exception(Throwable $e): ResponseInterface
    {
        $status = 500;

        if ($e instanceof \InvalidArgumentException) {
            $status = 400;
        } elseif ($e instanceof \RuntimeException) {
            $status = 500;
        }

        return self::error($e->getMessage(), $status);
    }

    private static function jsonFlags(): int
    {
        return JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
    }
}
