<?php

namespace App\Controllers\Clients;

use App\Services\Clients\ClientService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Core\Request;
use App\Core\Response;

class ClientController
{
    private ClientService $clientService;

    public function __construct()
    {
        $this->clientService = new ClientService();
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $result = $this->clientService->getAllClients();
        return Response::json($result);
    }

    public function edit(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        $result = $this->clientService->getClientForEdit($id);
        return Response::json($result);
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        $result = $this->clientService->deleteClient($id);
        return Response::json($result);
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $id = (int) $request->getAttribute('id');
        $data = (new Request($request))->all();
        $result = $this->clientService->updateClient($id, $data);
        return Response::json($result);
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = (new Request($request))->all();
        $result = $this->clientService->createClient($data);
        return Response::created($result);
    }
}
