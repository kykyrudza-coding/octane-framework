<?php

declare(strict_types=1);

namespace Tests\Prism;

use Horizon\Contracts\Prism\ViewFactoryContract;
use Horizon\Prism\RenderedView;
use Horizon\Prism\View;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    public function test_view_with_data_delegates_to_factory(): void
    {
        $factory = $this->createMock(ViewFactoryContract::class);
        
        $renderedView = new RenderedView('Output');

        $factory->expects($this->any())
            ->method('make')
            ->with('home', ['title' => 'Test', 'user' => 'John'])
            ->willReturn($renderedView);

        $view = new View($factory, 'home', ['title' => 'Test']);
        $view->with('user', 'John');

        $this->assertSame('Output', $view->render());
        $this->assertSame('Output', (string) $view);
    }

    public function test_with_array_merges_data(): void
    {
        $factory = $this->createMock(ViewFactoryContract::class);
        $renderedView = new RenderedView('Output');

        $factory->expects($this->once())
            ->method('make')
            ->with('home', ['a' => 1, 'b' => 2, 'c' => 3])
            ->willReturn($renderedView);

        $view = new View($factory, 'home', ['a' => 1]);
        $view->with(['b' => 2, 'c' => 3]);

        $this->assertSame('Output', $view->render());
    }
}
