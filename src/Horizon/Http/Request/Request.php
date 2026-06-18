<?php

declare(strict_types=1);

namespace Horizon\Http\Request;

use Horizon\Contracts\Http\Request\RequestContract;
use Horizon\Contracts\Validation\ValidatedDataContract;
use Horizon\Validation\ValidatorFactory;

class Request implements RequestContract
{
    /**
     * @var array<string, mixed>
     */
    protected array $query;

    /**
     * @var array<string, mixed>
     */
    protected array $payload;

    /**
     * @var array<string, mixed>
     */
    protected array $files;

    /**
     * @var array<string, mixed>
     */
    protected array $server;

    /**
     * @var array<string, mixed>
     */
    protected array $cookies;

    public function __construct()
    {
        $this->createFromGlobals();
    }

    protected function createFromGlobals(): void
    {
        $this->query = $this->normalizeStringKeyArray($_GET);
        $this->payload = $this->normalizeStringKeyArray($_POST);
        $this->files = $this->normalizeStringKeyArray($_FILES);
        $this->server = $this->normalizeStringKeyArray($_SERVER);
        $this->cookies = $this->normalizeStringKeyArray($_COOKIE);
    }

    public function method(): string
    {
        $method = $this->server['REQUEST_METHOD'] ?? 'GET';

        return strtoupper(is_string($method) ? $method : 'GET');
    }

    public function uri(): string
    {
        $requestUri = $this->server['REQUEST_URI'] ?? '/';
        $uri = parse_url(is_string($requestUri) ? $requestUri : '/', PHP_URL_PATH);

        return is_string($uri) ? $uri : '/';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $this->query[$key] ?? $default;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }

    public function file(string $key, mixed $default = null): mixed
    {
        return $this->files[$key] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_merge($this->query, $this->payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function allQuery(): array
    {
        return $this->query;
    }

    /**
     * @return array<string, mixed>
     */
    public function allPayload(): array
    {
        return $this->payload;
    }

    public function has(string $key): bool
    {
        return isset($this->payload[$key]) || isset($this->query[$key]);
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    public function validate(array $rules): ValidatedDataContract
    {
        return (new ValidatorFactory)
            ->make($this->all(), $rules)
            ->validate();
    }

    /**
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $payload
     */
    public function replace(array $query = [], array $payload = []): void
    {
        if ($query !== []) {
            $this->query = $query;
        }

        if ($payload !== []) {
            $this->payload = $payload;
        }
    }

    /**
     * @param  array<mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeStringKeyArray(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }
}
