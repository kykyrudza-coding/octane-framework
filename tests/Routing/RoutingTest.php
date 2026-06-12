<?php

declare(strict_types=1);

namespace Tests\Routing;

use Horizon\Routing\PendingRoute;
use Horizon\Routing\RouteCollection;
use Horizon\Routing\Router;
use Horizon\Routing\RouteRegistrar;
use LogicException;
use PHPUnit\Framework\TestCase;

class RoutingTest extends TestCase
{
    private RouteCollection $collection;

    private Router $router;

    private RouteRegistrar $registrar;

    protected function setUp(): void
    {
        $this->collection = new RouteCollection;
        $this->router = new Router($this->collection);
        $this->registrar = new RouteRegistrar($this->router);
    }

    public function test_register_and_match_get_route(): void
    {
        $this->registrar->get('/hello', fn () => 'world');
        $match = $this->router->match('GET', '/hello');

        $this->assertNotNull($match);
        $this->assertSame('/hello', $match->getRoute()->uri());
        $this->assertContains('GET', $match->getRoute()->methods());
    }

    public function test_different_http_methods(): void
    {
        $this->registrar->post('/items', fn () => 'create');
        $this->registrar->put('/items', fn () => 'update');
        $this->registrar->delete('/items', fn () => 'delete');

        $this->assertNotNull($this->router->match('POST', '/items'));
        $this->assertNotNull($this->router->match('PUT', '/items'));
        $this->assertNotNull($this->router->match('DELETE', '/items'));
        $this->assertNull($this->router->match('GET', '/items'));
    }

    public function test_parametrized_route_extracts_params(): void
    {
        $this->registrar->get('/posts/{id}', fn () => 'post');
        $match = $this->router->match('GET', '/posts/42');

        $this->assertNotNull($match);
        $this->assertSame(['id' => '42'], $match->getParams());
    }

    public function test_multiple_route_params(): void
    {
        $this->registrar->get('/blog/{category}/{slug}', fn () => 'post');
        $match = $this->router->match('GET', '/blog/tech/hello-world');

        $this->assertNotNull($match);
        $this->assertSame(['category' => 'tech', 'slug' => 'hello-world'], $match->getParams());
    }

    public function test_no_match_returns_null(): void
    {
        $this->registrar->get('/existing', fn () => 'ok');

        $this->assertNull($this->router->match('GET', '/nonexistent'));
        $this->assertNull($this->router->match('POST', '/existing'));
    }

    public function test_group_prefix(): void
    {
        $this->registrar->prefix('/api')->group(function (RouteRegistrar $r) {
            $r->get('/users', fn () => 'users');
            $r->get('/posts', fn () => 'posts');
        });

        $this->assertNotNull($this->router->match('GET', '/api/users'));
        $this->assertNotNull($this->router->match('GET', '/api/posts'));
        $this->assertNull($this->router->match('GET', '/users'));
    }

    public function test_group_middleware(): void
    {
        $this->registrar->middleware(['auth', 'verified'])->group(function (RouteRegistrar $r) {
            $r->get('/dashboard', fn () => 'dash');
        });

        $match = $this->router->match('GET', '/dashboard');
        $this->assertNotNull($match);
        $this->assertSame(['auth', 'verified'], $match->getRoute()->middleware());
    }

    public function test_named_route_via_pending(): void
    {
        $pending = new PendingRoute(
            router: $this->router,
            methods: ['GET'],
            uri: '/named',
            action: fn () => 'named',
        );
        $pending->name('custom.name');
        $dto = $pending->register();

        $this->assertSame('custom.name', $dto->name());
        $this->assertNotNull($this->collection->getByName('custom.name'));
    }

    public function test_pending_route_registers_when_destroyed(): void
    {
        $pending = $this->registrar->get('/auto', fn () => 'auto');

        $this->assertNull($this->router->match('GET', '/auto'));

        unset($pending);

        $match = $this->router->match('GET', '/auto');
        $this->assertNotNull($match);
        $this->assertSame('/auto', $match->getRoute()->uri());
    }

    public function test_pending_route_destructor_does_not_duplicate_manually_registered_route(): void
    {
        $pending = $this->registrar->get('/manual', fn () => 'manual');
        $pending->register();

        $this->assertCount(1, $this->collection->all());

        unset($pending);

        $this->assertCount(1, $this->collection->all());
        $this->assertNotNull($this->router->match('GET', '/manual'));
    }

    public function test_pending_route_cannot_be_registered_twice(): void
    {
        $pending = $this->registrar->get('/twice', fn () => 'twice');
        $pending->register();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Route has already been registered.');

        $pending->register();
    }

    public function test_auto_registered_route_keeps_name_prefix_and_middleware(): void
    {
        $pending = $this->registrar
            ->prefix('/admin')
            ->name('admin.')
            ->middleware(['auth'])
            ->get('/users', fn () => 'users')
            ->name('users.index');

        unset($pending);

        $route = $this->collection->getByName('admin.users.index');

        $this->assertNotNull($route);
        $this->assertSame('/admin/users', $route->uri());
        $this->assertSame(['auth'], $route->middleware());
        $this->assertNotNull($this->router->match('GET', '/admin/users'));
    }

    public function test_auto_registered_routes_restore_outer_group_state(): void
    {
        $this->registrar->prefix('/api')->group(function (RouteRegistrar $r) {
            $inner = $r->prefix('/v1')->get('/users', fn () => 'users');
            unset($inner);
        });

        $outside = $this->registrar->get('/status', fn () => 'ok');
        unset($outside);

        $this->assertNotNull($this->router->match('GET', '/api/v1/users'));
        $this->assertNotNull($this->router->match('GET', '/status'));
        $this->assertNull($this->router->match('GET', '/api/status'));
    }

    public function test_fallback_route_auto_registers_when_destroyed(): void
    {
        $pending = $this->registrar->fallback(fn () => 'fallback');

        $this->assertNull($this->router->match('GET', '/missing'));

        unset($pending);

        $match = $this->router->match('GET', '/missing');
        $this->assertNotNull($match);
        $this->assertSame('/{fallback}', $match->getRoute()->uri());
        $this->assertSame(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $match->getRoute()->methods());
    }

    public function test_route_collection_all(): void
    {
        $this->registrar->get('/a', fn () => 'a');
        $this->registrar->post('/b', fn () => 'b');
        $this->registrar->delete('/c', fn () => 'c');

        $this->assertCount(3, $this->collection->all());
    }

    public function test_nested_groups_accumulate_prefix(): void
    {
        $this->registrar->prefix('/api')->group(function (RouteRegistrar $r) {
            $r->prefix('/v1')->group(function (RouteRegistrar $r) {
                $r->get('/users', fn () => 'users');
            });
        });

        $this->assertNotNull($this->router->match('GET', '/api/v1/users'));
    }
}
