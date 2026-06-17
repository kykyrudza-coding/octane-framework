<?php

declare(strict_types=1);

namespace Horizon\Arch\Http\Pipes;

use Closure;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Contracts\Routing\RouterContract;
use Horizon\Support\Pipeline\PipeInterface;

class ResolveRoute implements PipeInterface
{
    public function __construct(
        protected RouterContract $router,
    ) {}

    /**
     * @param  RequestContextContract  $payload
     * @param  Closure(RequestContextContract): mixed  $next
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        $method = $payload->getRequest()->method();
        $uri = $payload->getRequest()->uri();

        $match = $this->router->match($method, $uri);

        if ($match === null) {
            abort(404, 'Route not found.');
        }

        $payload->setRoute($match->getRoute());
        $payload->setParams($match->getParams());

        return $next($payload);
    }
}
