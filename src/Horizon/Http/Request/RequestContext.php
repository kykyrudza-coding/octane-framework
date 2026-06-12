<?php

declare(strict_types=1);

namespace Horizon\Http\Request;

use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Contracts\Http\Request\RequestContract;
use Horizon\Contracts\Http\Response\ResponseContract;
use Horizon\Contracts\Routing\RouteDtoContract;
use RuntimeException;

final class RequestContext implements RequestContextContract
{
    protected ?RouteDtoContract $route = null;

    /**
     * @var array<string, string>
     */
    protected array $params = [];

    protected ?ResponseContract $response = null;

    public function __construct(
        protected readonly RequestContract $request,
    ) {}

    public static function capture(): static
    {
        return new self(new Request);
    }

    public function getRequest(): RequestContract
    {
        return $this->request;
    }

    public function getRoute(): ?RouteDtoContract
    {
        return $this->route;
    }

    /**
     * @return array<string, string>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function getParam(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function getResponse(): ResponseContract
    {
        if ($this->response === null) {
            throw new RuntimeException('Response has not been set on the request context.');
        }

        return $this->response;
    }

    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    public function setRoute(?RouteDtoContract $route): void
    {
        $this->route = $route;
    }

    /**
     * @param  array<string, string>  $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function setResponse(ResponseContract $response): void
    {
        $this->response = $response;
    }
}
