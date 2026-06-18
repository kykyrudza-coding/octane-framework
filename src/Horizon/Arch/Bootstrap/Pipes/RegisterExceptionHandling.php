<?php

declare(strict_types=1);

namespace Horizon\Arch\Bootstrap\Pipes;

use Closure;
use Horizon\Arch\Bootstrap\ApplicationBuilder;
use Horizon\Arch\Exceptions\BindingResolutionException;
use Horizon\Contracts\Exception\HandlerContract;
use Horizon\Contracts\Support\Providers\ServiceProviderContract;
use Horizon\Support\Pipeline\PipeInterface;

class RegisterExceptionHandling implements PipeInterface
{
    /**
     * @param  ApplicationBuilder  $payload
     * @param  Closure(ApplicationBuilder): mixed  $next
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        $file = __DIR__.'/../../../Exception/components.json';

        if (is_file($file)) {
            $data = json_decode((string) file_get_contents($file), true);
            $provider = is_array($data) ? ($data['provider'] ?? null) : null;

            if (is_string($provider) && is_subclass_of($provider, ServiceProviderContract::class)) {
                /** @var class-string<ServiceProviderContract> $provider */
                $payload->app->registerProvider(new $provider($payload->app));
            }
        }

        $handler = $payload->app->make(HandlerContract::class);
        if (! $handler instanceof HandlerContract) {
            throw new BindingResolutionException('Exception handler binding must resolve to a HandlerContract instance.');
        }

        $handler->register();

        return $next($payload);
    }
}
