<?php

namespace App\Core\Middleware;

use App\Core\Response;
use App\Core\JWT;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Throwable;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $token = null;

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = trim(str_replace('Bearer', '', $authHeader));
        }

        if (!$token && isset($_COOKIE['token'])) {
            $token = $_COOKIE['token'];
        }

        if (!$token) {
            return Response::error('Token JWT não informado. Usuário não autenticado.', 401);
        }

        try {
            $payload = JWT::verify($token);
        } catch (\Firebase\JWT\ExpiredException $e) {
            return Response::error('Token expirado.', 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return Response::error('Assinatura do token inválida.', 401);
        } catch (\Throwable $e) {
            return Response::error('Token JWT inválido.', 401);
        }

        $request = $request->withAttribute('jwt_payload', $payload);
        return $handler->handle($request);
    }

}
