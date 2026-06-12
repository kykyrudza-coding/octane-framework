<?php

declare(strict_types=1);

namespace Horizon\Arch\Http\Handle;

use Closure;
use Horizon\Arch\Pipeline\PipeInterface;
use Horizon\Contracts\Arch\Container\ContainerContract;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Contracts\Http\Request\RequestContract;
use Horizon\Contracts\Http\Response\ResponseContract;
use Horizon\Contracts\Http\Response\ResponseFactoryContract;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;
use Stringable;

class InvokeController implements PipeInterface
{
    public function __construct(
        protected ContainerContract $container,
    ) {}

    /**
     * @param  RequestContextContract  $payload
     * @param  Closure(RequestContextContract): mixed  $next
     *
     * @throws ReflectionException
     */
    public function handle(mixed $payload, Closure $next): mixed
    {
        $route = $payload->getRoute();

        if ($route === null) {
            throw new RuntimeException('Cannot invoke controller before a route is resolved.');
        }

        $action = $route->action();
        $params = $payload->getParams();

        $result = match (true) {
            $action instanceof Closure => $action(...$this->resolveFunctionArguments($action, $payload, $params)),
            is_array($action) => $this->invokeArrayAction($action, $payload, $params),
            default => $this->invokeStringAction($action, $payload, $params),
        };

        if (! $result instanceof ResponseContract) {
            $factory = $this->container->make(ResponseFactoryContract::class);
            if (! $factory instanceof ResponseFactoryContract) {
                throw new RuntimeException('Response factory binding must resolve to a ResponseFactoryContract instance.');
            }

            $result = $factory->make($this->responseBody($result));
        }

        $payload->setResponse($result);

        return $next($payload);
    }

    /**
     * @param  array{0: class-string, 1: string}  $action
     * @param  array<string, string>  $params
     *
     * @throws ReflectionException
     */
    protected function invokeArrayAction(array $action, RequestContextContract $context, array $params): mixed
    {
        [$controllerClass, $method] = $action;

        $controller = $this->container->make($controllerClass);

        if (! is_object($controller) || ! method_exists($controller, $method)) {
            throw new RuntimeException("Controller action $controllerClass@$method is not callable.");
        }

        return $controller->{$method}(...$this->resolveMethodArguments($controller, $method, $context, $params));
    }

    /**
     * @param  array<string, string>  $params
     *
     * @throws ReflectionException
     */
    protected function invokeStringAction(string $action, RequestContextContract $context, array $params): mixed
    {
        if (! str_contains($action, '@')) {
            throw new RuntimeException("Invalid string action format: '$action'. Expected 'Controller@method'.");
        }

        [$controllerClass, $method] = explode('@', $action, 2);

        $controller = $this->container->make($controllerClass);

        if (! is_object($controller) || ! method_exists($controller, $method)) {
            throw new RuntimeException("Controller action $controllerClass@$method is not callable.");
        }

        return $controller->{$method}(...$this->resolveMethodArguments($controller, $method, $context, $params));
    }

    /**
     * @param  array<string, string>  $routeParams
     * @return list<mixed>
     *
     * @throws ReflectionException
     */
    protected function resolveMethodArguments(
        object $controller,
        string $method,
        RequestContextContract $context,
        array $routeParams
    ): array {
        $reflection = new ReflectionMethod($controller, $method);

        return $this->resolveParameters($reflection->getParameters(), $context, $routeParams);
    }

    /**
     * @param  array<string, string>  $routeParams
     * @return list<mixed>
     *
     * @throws ReflectionException
     */
    protected function resolveFunctionArguments(
        Closure $closure,
        RequestContextContract $context,
        array $routeParams
    ): array {
        $reflection = new ReflectionFunction($closure);

        return $this->resolveParameters($reflection->getParameters(), $context, $routeParams);
    }

    /**
     * @param  list<ReflectionParameter>  $parameters
     * @param  array<string, string>  $routeParams
     * @return list<mixed>
     */
    protected function resolveParameters(array $parameters, RequestContextContract $context, array $routeParams): array
    {
        $arguments = [];
        $positionalRouteParams = array_values($routeParams);

        foreach ($parameters as $index => $parameter) {
            $arguments[] = $this->resolveParameter($parameter, $context, $routeParams, $positionalRouteParams, $index);
        }

        return $arguments;
    }

    /**
     * @param  array<string, string>  $routeParams
     * @param  list<string>  $positionalRouteParams
     */
    protected function resolveParameter(
        ReflectionParameter $parameter,
        RequestContextContract $context,
        array $routeParams,
        array $positionalRouteParams,
        int $position
    ): mixed {
        $name = $parameter->getName();

        if (array_key_exists($name, $routeParams)) {
            return $routeParams[$name];
        }

        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            $typeName = $type->getName();

            if (is_a($context->getRequest(), $typeName)) {
                return $context->getRequest();
            }

            if ($typeName === RequestContract::class) {
                return $context->getRequest();
            }

            return $this->container->make($typeName);
        }

        if (array_key_exists($position, $positionalRouteParams)) {
            return $positionalRouteParams[$position];
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        throw new RuntimeException("Cannot resolve controller parameter \$$name.");
    }

    protected function responseBody(mixed $result): string
    {
        if ($result === null || is_scalar($result) || $result instanceof Stringable) {
            return (string) $result;
        }

        throw new RuntimeException('Controller result must be a ResponseContract, scalar, stringable, or null.');
    }
}
