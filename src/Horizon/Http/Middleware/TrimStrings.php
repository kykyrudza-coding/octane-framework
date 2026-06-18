<?php

declare(strict_types=1);

namespace Horizon\Http\Middleware;

use Closure;
use Horizon\Contracts\Http\Middleware\MiddlewareContract;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Contracts\Http\Response\ResponseContract;
use Horizon\Http\Exceptions\MiddlewareException;

class TrimStrings implements MiddlewareContract
{
    /**
     * Keys that should not be trimmed (e.g. passwords).
     */
    /**
     * @var list<string>
     */
    protected array $except = [
        'password',
        'password_confirmation',
    ];

    public function handle(RequestContextContract $context, Closure $next): ResponseContract
    {
        $request = $context->getRequest();

        $request->replace(
            query: $this->clean($request->allQuery()),
            payload: $this->clean($request->allPayload()),
        );

        $response = $next($context);
        if (! $response instanceof ResponseContract) {
            throw new MiddlewareException('Middleware chain must return a ResponseContract instance.');
        }

        return $response;
    }

    /**
     * @template TKey of array-key
     *
     * @param  array<TKey, mixed>  $data
     * @return array<TKey, mixed>
     */
    protected function clean(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = match (true) {
                in_array($key, $this->except, true) => $value,
                is_string($value) => trim($value),
                is_array($value) => $this->clean($value),
                default => $value,
            };
        }

        return $data;
    }
}
