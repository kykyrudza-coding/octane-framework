<?php

declare(strict_types=1);

namespace Horizon\Contracts\Http\Request;

use Horizon\Contracts\Http\Response\ResponseContract;
use Horizon\Contracts\Routing\RouteDtoContract;

interface RequestContextContract
{
    public static function capture(): static;

    public function getRequest(): RequestContract;

    public function getRoute(): ?RouteDtoContract;

    /**
     * @return array<string, string>
     */
    public function getParams(): array;

    public function getParam(string $key, mixed $default = null): mixed;

    public function getResponse(): ResponseContract;

    public function hasResponse(): bool;

    public function setRoute(?RouteDtoContract $route): void;

    /**
     * @param  array<string, string>  $params
     */
    public function setParams(array $params): void;

    public function setResponse(ResponseContract $response): void;
}
