<?php

declare(strict_types=1);

namespace Horizon\Contracts\Http\Middleware;

interface MiddlewareCollectionContract
{
    /**
     * @param  list<string>  $middleware
     */
    public function global(array $middleware): static;

    /**
     * @param  list<string>  $middleware
     */
    public function web(array $middleware): static;

    /**
     * @param  list<string>  $middleware
     */
    public function api(array $middleware): static;

    /**
     * @param  list<string>  $middleware
     */
    public function console(array $middleware): static;

    /**
     * @return list<string>
     */
    public function getGlobal(): array;

    /**
     * @return list<string>
     */
    public function getWeb(): array;

    /**
     * @return list<string>
     */
    public function getApi(): array;

    /**
     * @return list<string>
     */
    public function getConsole(): array;

    /**
     * @return list<string>
     */
    public function getGroup(string $group = 'web'): array;
}
