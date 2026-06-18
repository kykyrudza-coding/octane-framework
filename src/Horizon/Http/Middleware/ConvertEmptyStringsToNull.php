<?php

declare(strict_types=1);

namespace Horizon\Http\Middleware;

use Closure;
use Horizon\Contracts\Http\Middleware\MiddlewareContract;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Contracts\Http\Response\ResponseContract;
use Horizon\Http\Exceptions\MiddlewareException;

class ConvertEmptyStringsToNull implements MiddlewareContract
{
    public function handle(RequestContextContract $context, Closure $next): ResponseContract
    {
        $request = $context->getRequest();

        $request->replace(
            query: $this->convert($request->allQuery()),
            payload: $this->convert($request->allPayload()),
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
    protected function convert(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = match (true) {
                $value === '' => null,
                is_array($value) => $this->convert($value),
                default => $value,
            };
        }

        return $data;
    }
}
