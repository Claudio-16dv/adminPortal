<?php

namespace App\Core;

use Psr\Http\Message\ServerRequestInterface;

class Request
{
    protected ServerRequestInterface $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function all(): array
    {
        $contentType = $this->request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $raw = (string) $this->request->getBody();
            $data = json_decode($raw, true);

            return is_array($data) ? $data : [];
        }

        return $this->request->getParsedBody() ?? [];
    }

    public function get(string $key, $default = null)
    {
        $body = $this->all();
        return $body[$key] ?? $default;
    }

    public function query(string $key, $default = null)
    {
        $query = $this->request->getQueryParams();
        return $query[$key] ?? $default;
    }

    public function header(string $key): ?string
    {
        $headers = $this->request->getHeader($key);
        return $headers[0] ?? null;
    }

    public function raw(): string
    {
        return (string) $this->request->getBody();
    }

    public function getOriginal(): ServerRequestInterface
    {
        return $this->request;
    }
}
