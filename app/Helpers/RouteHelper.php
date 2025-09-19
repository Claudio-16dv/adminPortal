<?php

namespace App\Helpers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Core\Middleware\AuthMiddleware;

/**
 * Envolve um handler com o AuthMiddleware.
 *
 * @param callable $handler
 * @return callable
 */
function protectedRoute(callable $handler): callable {
    return function (ServerRequestInterface $request, ResponseInterface $response) use ($handler): ResponseInterface {
        $authMiddleware = new AuthMiddleware();
        
        $finalHandler = new class($handler, $response) implements RequestHandlerInterface {
            private $handler;
            private $response;
            public function __construct(callable $handler, ResponseInterface $response)
            {
                $this->handler = $handler;
                $this->response = $response;
            }
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return call_user_func_array($this->handler, [$request, $this->response]);
            }
        };

        return $authMiddleware->process($request, $finalHandler);
    };
}
