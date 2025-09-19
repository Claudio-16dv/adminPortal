<?php

namespace App\Core;

use FastRoute\RouteCollector;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use DI\ContainerBuilder;
use App\Core\Middleware\CorsMiddleware;

class Kernel
{
    private $dispatcher;
    private $container;
    private $middlewareQueue = [];
    private ServerRequestInterface $request;

    public function __construct(array $middlewareQueue, ServerRequestInterface $request)
    {
        $this->middlewareQueue = $middlewareQueue;
        $this->request = $request;

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $this->container = $containerBuilder->build();

        $this->dispatcher = $this->createDispatcher();
    }

    private function createDispatcher()
    {
        return \FastRoute\simpleDispatcher(function(RouteCollector $r) {
            $routes = require __DIR__ . '/../Routes/api.php';
            $routes($r);
        });
    }

    public function run(): void
    {
        $finalHandler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {

                return new Response(404, ['Content-Type' => 'text/plain'], 'Not Found');
            }
        };

        if ($this->request->getMethod() !== 'OPTIONS') {
            $httpMethod = $this->request->getMethod();
            $uri = $this->request->getUri()->getPath();
            $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

            if ($routeInfo[0] === \FastRoute\Dispatcher::FOUND) {
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                foreach ($vars as $key => $value) {
                    $this->request = $this->request->withAttribute($key, $value);
                }

                $response = new \Nyholm\Psr7\Response();
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
            }
        }

        $pipeline = array_reduce(
            array_reverse($this->middlewareQueue),
            function (RequestHandlerInterface $next, $middleware) {
                return new class($middleware, $next) implements RequestHandlerInterface {
                    private $middleware;
                    private $next;
                    
                    public function __construct($middleware, RequestHandlerInterface $next)
                    {
                        $this->middleware = $middleware;
                        $this->next = $next;
                    }
                    
                    public function handle(ServerRequestInterface $request): ResponseInterface
                    {
                        return $this->middleware->process($request, $this->next);
                    }
                };
            },
            $finalHandler
        );

        $response = $pipeline->handle($this->request);

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
        
        http_response_code($response->getStatusCode());
        echo $response->getBody();
    }  
}
