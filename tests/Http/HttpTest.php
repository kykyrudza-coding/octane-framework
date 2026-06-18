<?php

declare(strict_types=1);

namespace Tests\Http;

use Horizon\Arch\Container;
use Horizon\Arch\Http\Pipes\InvokeController;
use Horizon\Contracts\Http\Request\RequestContract;
use Horizon\Contracts\Validation\ValidatedDataContract;
use Horizon\Http\Request\Request;
use Horizon\Http\Request\RequestContext;
use Horizon\Http\Response\Response;
use Horizon\Routing\RouteDTO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class HttpTest extends TestCase
{
    public function test_response_default_status_and_body(): void
    {
        $response = new Response('Hello');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Hello', $response->getBody());
    }

    public function test_response_with_status_is_immutable(): void
    {
        $original = new Response('body', 200);
        $modified = $original->withStatus(404);

        $this->assertSame(200, $original->getStatusCode());
        $this->assertSame(404, $modified->getStatusCode());
        $this->assertNotSame($original, $modified);
    }

    public function test_response_with_body_is_immutable(): void
    {
        $original = new Response('old');
        $modified = $original->withBody('new');

        $this->assertSame('old', $original->getBody());
        $this->assertSame('new', $modified->getBody());
    }

    public function test_response_with_header_normalizes_name(): void
    {
        $response = new Response;
        $modified = $response->withHeader('x-custom-header', 'value');

        $this->assertSame('value', $modified->getHeader('X-Custom-Header'));
        $this->assertSame('value', $modified->getHeader('x-custom-header'));
    }

    public function test_response_default_content_type_header(): void
    {
        $response = new Response;

        $this->assertSame('text/html; charset=UTF-8', $response->getHeader('Content-Type'));
    }

    public function test_response_with_headers_merges(): void
    {
        $response = (new Response)->withHeaders([
            'X-Foo' => 'bar',
            'X-Baz' => 'qux',
        ]);

        $this->assertSame('bar', $response->getHeader('X-Foo'));
        $this->assertSame('qux', $response->getHeader('X-Baz'));
        // Original content-type should still be there
        $this->assertSame('text/html; charset=UTF-8', $response->getHeader('Content-Type'));
    }

    public function test_request_context_route_getters_and_setters(): void
    {
        $context = $this->createRequestContext();
        $route = new RouteDTO(['GET'], '/test', fn () => 'ok');

        $this->assertNull($context->getRoute());

        $context->setRoute($route);
        $this->assertSame($route, $context->getRoute());
        $this->assertSame('/test', $context->getRoute()->uri());
    }

    public function test_request_context_params(): void
    {
        $context = $this->createRequestContext();

        $this->assertSame([], $context->getParams());

        $context->setParams(['id' => '42', 'slug' => 'hello']);

        $this->assertSame('42', $context->getParam('id'));
        $this->assertSame('hello', $context->getParam('slug'));
        $this->assertSame('default', $context->getParam('missing', 'default'));
    }

    public function test_request_context_response_throws_when_not_set(): void
    {
        $context = $this->createRequestContext();

        $this->assertFalse($context->hasResponse());

        $this->expectException(RuntimeException::class);
        $context->getResponse();
    }

    public function test_request_context_response_set_and_get(): void
    {
        $context = $this->createRequestContext();
        $response = new Response('OK', 200);

        $context->setResponse($response);

        $this->assertTrue($context->hasResponse());
        $this->assertSame($response, $context->getResponse());
        $this->assertSame('OK', $context->getResponse()->getBody());
    }

    public function test_controller_action_receives_request_and_container_dependencies(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/posts/42';

        $context = new RequestContext(new Request);
        $context->setRoute(new RouteDTO(
            methods: ['GET'],
            uri: '/posts/{id}',
            action: [ControllerWithInjectedAction::class, 'show'],
        ));
        $context->setParams(['id' => '42']);

        $response = (new InvokeController(new Container))->handle(
            $context,
            fn (RequestContext $context): Response => $context->getResponse(),
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('GET:/posts/42:posts:42', $response->getBody());
    }

    public function test_closure_action_receives_request_dependency_and_route_params(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/posts/99';

        $context = new RequestContext(new Request);
        $context->setRoute(new RouteDTO(
            methods: ['POST'],
            uri: '/posts/{id}',
            action: fn (Request $request, string $id): Response => new Response($request->method().':'.$id),
        ));
        $context->setParams(['id' => '99']);

        $response = (new InvokeController(new Container))->handle(
            $context,
            fn (RequestContext $context): Response => $context->getResponse(),
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('POST:99', $response->getBody());
    }

    /**
     * RequestContext::capture() relies on $_SERVER globals.
     * For unit tests, we create RequestContext with a mock Request.
     */
    private function createRequestContext(): RequestContext
    {
        $request = new class implements RequestContract
        {
            public function method(): string
            {
                return 'GET';
            }

            public function uri(): string
            {
                return '/';
            }

            public function input(string $key, mixed $default = null): mixed
            {
                return $default;
            }

            public function get(string $key, mixed $default = null): mixed
            {
                return $default;
            }

            public function post(string $key, mixed $default = null): mixed
            {
                return $default;
            }

            public function all(): array
            {
                return [];
            }

            public function allQuery(): array
            {
                return [];
            }

            public function allPayload(): array
            {
                return [];
            }

            public function replace(array $query = [], array $payload = []): void {}

            public function has(string $key): bool
            {
                return false;
            }

            public function isMethod(string $method): bool
            {
                return $method === 'GET';
            }

            public function isGet(): bool
            {
                return true;
            }

            public function isPost(): bool
            {
                return false;
            }

            public function validate(array $rules): ValidatedDataContract
            {
                throw new RuntimeException('Validation is not available in this request test double.');
            }

            public function file(string $key, mixed $default = null): mixed
            {
                return $default;
            }

            public function cookie(string $key, mixed $default = null): mixed
            {
                return $default;
            }

            public function server(string $key, mixed $default = null): mixed
            {
                return $default;
            }
        };

        return new RequestContext($request);
    }
}

class ControllerWithInjectedAction
{
    public function show(Request $request, PostRepositoryForInjection $repository, string $id): Response
    {
        return new Response($request->method().':'.$request->uri().':'.$repository->name().':'.$id);
    }
}

class PostRepositoryForInjection
{
    public function name(): string
    {
        return 'posts';
    }
}
