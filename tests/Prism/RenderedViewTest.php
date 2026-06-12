<?php

declare(strict_types=1);

namespace Tests\Prism;

use Horizon\Prism\RenderedView;
use PHPUnit\Framework\TestCase;

class RenderedViewTest extends TestCase
{
    public function test_render_returns_content(): void
    {
        $view = new RenderedView('Static Content');

        $this->assertSame('Static Content', $view->render());
        $this->assertSame('Static Content', (string) $view);
    }

    public function test_with_is_no_op(): void
    {
        $view = new RenderedView('Static Content');
        $view->with('key', 'value');

        $this->assertSame('Static Content', $view->render());
    }
}
