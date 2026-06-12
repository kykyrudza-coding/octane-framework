<?php

declare(strict_types=1);

namespace Horizon\Http\Collection;

use Horizon\Contracts\Http\Middleware\MiddlewareCollectionContract;

class MiddlewareCollection implements MiddlewareCollectionContract
{
    /**
     * @var list<string>
     */
    protected array $global = [];

    /**
     * @var list<string>
     */
    protected array $web = [];

    /**
     * @var list<string>
     */
    protected array $api = [];

    /**
     * @var list<string>
     */
    protected array $console = [];

    /**
     * @param  list<string>  $middleware
     */
    public function global(array $middleware): static
    {
        $this->global = array_merge($this->global, $middleware);

        return $this;
    }

    /**
     * @param  list<string>  $middleware
     */
    public function web(array $middleware): static
    {
        $this->web = array_merge($this->web, $middleware);

        return $this;
    }

    /**
     * @param  list<string>  $middleware
     */
    public function api(array $middleware): static
    {
        $this->api = array_merge($this->api, $middleware);

        return $this;
    }

    /**
     * @param  list<string>  $middleware
     */
    public function console(array $middleware): static
    {
        $this->console = array_merge($this->console, $middleware);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getGlobal(): array
    {
        return $this->global;
    }

    /**
     * @return list<string>
     */
    public function getWeb(): array
    {
        return $this->web;
    }

    /**
     * @return list<string>
     */
    public function getApi(): array
    {
        return $this->api;
    }

    /**
     * @return list<string>
     */
    public function getConsole(): array
    {
        return $this->console;
    }

    /**
     * @return list<string>
     */
    public function getGroup(string $group = 'web'): array
    {
        return match ($group) {
            'web' => $this->web,
            'api' => $this->api,
            'console' => $this->console,
            default => $this->global,
        };
    }
}
