<?php

declare(strict_types=1);

namespace Horizon\Contracts\Http\Request;

interface RequestContract
{
    public function method(): string;

    public function uri(): string;

    public function input(string $key, mixed $default = null): mixed;

    public function get(string $key, mixed $default = null): mixed;

    public function post(string $key, mixed $default = null): mixed;

    public function file(string $key, mixed $default = null): mixed;

    public function cookie(string $key, mixed $default = null): mixed;

    public function server(string $key, mixed $default = null): mixed;

    /**
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * @return array<string, mixed>
     */
    public function allQuery(): array;

    /**
     * @return array<string, mixed>
     */
    public function allPayload(): array;

    /**
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $payload
     */
    public function replace(array $query = [], array $payload = []): void;

    public function has(string $key): bool;

    public function isMethod(string $method): bool;

    public function isGet(): bool;

    public function isPost(): bool;
}
