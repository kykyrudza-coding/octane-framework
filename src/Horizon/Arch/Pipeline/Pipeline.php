<?php

declare(strict_types=1);

namespace Horizon\Arch\Pipeline;

use Horizon\Contracts\Arch\Container\ContainerContract;
use RuntimeException;

class Pipeline
{
    /**
     * @var list<callable|string>
     */
    protected array $pipes = [];

    private mixed $payload;

    public function __construct(
        private readonly ContainerContract $container,
    ) {}

    public function send(mixed $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * @param  list<callable|string>  $pipes
     */
    public function through(array $pipes): static
    {
        $this->pipes = $pipes;

        return $this;
    }

    public function then(callable|string $destination): mixed
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            fn (callable $next, callable|string $pipe): callable => function (mixed $payload) use ($next, $pipe) {
                $handler = is_string($pipe)
                    ? $this->container->make($pipe)
                    : $pipe;

                if (! is_object($handler) || ! is_callable([$handler, 'handle'])) {
                    throw new RuntimeException('Pipeline pipe must resolve to an object with a callable handle method.');
                }

                return $handler->handle($payload, $next);
            },
            function (mixed $payload) use ($destination) {
                $handler = is_string($destination)
                    ? $this->container->make($destination)
                    : $destination;

                if (is_callable($handler)) {
                    return $handler($payload);
                }

                if ($handler instanceof PipeInterface) {
                    return $handler->handle($payload, static fn (mixed $value): mixed => $value);
                }

                throw new RuntimeException('Pipeline destination must be callable or resolve to a PipeInterface instance.');
            }
        );

        return $pipeline($this->payload);
    }
}
