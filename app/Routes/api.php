<?php

use App\Controllers\Auth\AuthController;
use App\Controllers\Clients\ClientController;
use function App\Helpers\protectedRoute;

return function (FastRoute\RouteCollector $r) {
    $authController = new AuthController();
    $clientController = new ClientController();

    $r->addRoute('POST', '/auth/create', [$authController, 'create']);
    $r->addRoute('POST', '/auth/login', [$authController, 'login']);
    $r->addRoute('POST', '/auth/logout', [$authController, 'logout']);

    $r->addRoute('GET', '/clients/list', protectedRoute([$clientController, 'list']));
    $r->addRoute('GET', '/clients/edit/{id}', protectedRoute([$clientController, 'edit']));
    $r->addRoute('DELETE', '/clients/delete/{id}', protectedRoute([$clientController, 'delete']));
    $r->addRoute('PUT', '/clients/update/{id}', protectedRoute([$clientController, 'update']));
    $r->addRoute('POST', '/clients/create', protectedRoute([$clientController, 'create']));
};
