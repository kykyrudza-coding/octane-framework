<?php

declare(strict_types=1);

namespace Tests\Exception;

use Horizon\Exception\Handler;
use Horizon\Exception\Renderers\ConsoleErrorRenderer;
use Horizon\Exception\Renderers\JsonErrorRenderer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ExceptionHandlerTest extends TestCase
{
    public function test_console_renderer_includes_class_and_message(): void
    {
        $renderer = new ConsoleErrorRenderer;
        $exception = new RuntimeException('Something failed');

        $output = $renderer->render($exception);

        $this->assertStringContainsString('RuntimeException', $output);
        $this->assertStringContainsString('Something failed', $output);
    }

    public function test_console_renderer_hides_trace_in_production(): void
    {
        $renderer = new ConsoleErrorRenderer;
        $exception = new RuntimeException('Oops');

        $debug = $renderer->render($exception);
        $prod = $renderer->render($exception, debug: false);

        $this->assertStringContainsString('#0', $debug); // trace
        $this->assertStringNotContainsString('#0', $prod);
    }

    public function test_console_renderer_content_type(): void
    {
        $renderer = new ConsoleErrorRenderer;

        $this->assertSame('text/plain', $renderer->contentType());
    }

    public function test_json_renderer_returns_valid_json(): void
    {
        $renderer = new JsonErrorRenderer;
        $exception = new RuntimeException('DB error');

        $output = $renderer->render($exception);
        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('error', $data);
        $this->assertSame('RuntimeException', $data['error']['type']);
        $this->assertSame('DB error', $data['error']['message']);
    }

    public function test_json_renderer_hides_details_in_production(): void
    {
        $renderer = new JsonErrorRenderer;
        $exception = new RuntimeException('Secret info');

        $output = $renderer->render($exception, debug: false);
        $data = json_decode($output, true);

        $this->assertSame('ServerError', $data['error']['type']);
        $this->assertSame('Something went wrong.', $data['error']['message']);
        $this->assertArrayNotHasKey('file', $data['error']);
        $this->assertArrayNotHasKey('trace', $data['error']);
    }

    public function test_json_renderer_content_type(): void
    {
        $renderer = new JsonErrorRenderer;

        $this->assertSame('application/json', $renderer->contentType());
    }

    public function test_handler_render_delegates_to_renderer(): void
    {
        $renderer = new ConsoleErrorRenderer;
        $handler = new Handler($renderer, debug: true);

        $exception = new RuntimeException('Test error');
        $output = $handler->render($exception);

        $this->assertStringContainsString('Test error', $output);
        $this->assertStringContainsString('RuntimeException', $output);
    }

    public function test_json_renderer_uses_exception_code_as_status(): void
    {
        $renderer = new JsonErrorRenderer;
        $exception = new RuntimeException('Not found', 404);

        $output = $renderer->render($exception);
        $data = json_decode($output, true);

        $this->assertSame(404, $data['error']['status']);
    }

    public function test_json_renderer_defaults_to_500_for_invalid_code(): void
    {
        $renderer = new JsonErrorRenderer;
        $exception = new RuntimeException('Error', 0);

        $output = $renderer->render($exception);
        $data = json_decode($output, true);

        $this->assertSame(500, $data['error']['status']);
    }
}
