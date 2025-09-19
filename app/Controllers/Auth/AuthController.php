<?php

namespace App\Controllers\Auth;

use App\Core\Request;
use App\Core\Response;
use App\Services\Auth\AuthService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = (new Request($request))->all();
        $result = $this->authService->login($data);
        return Response::json($result);
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $customRequest = new Request($request);
        $data = $customRequest->all();
        $result = $this->authService->createUser($data);
        return Response::created($result);
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $result = $this->authService->logout();
        return Response::json($result);
    }
}
