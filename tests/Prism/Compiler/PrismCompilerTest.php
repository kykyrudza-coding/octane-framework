<?php

declare(strict_types=1);

namespace Tests\Prism\Compiler;

use Horizon\Contracts\Prism\Prism\Compiler\DirectiveContract;
use Horizon\Contracts\Prism\Prism\Compiler\DirectiveRegistryContract;
use Horizon\Prism\Prism\Compiler\PrismCompiler;
use PHPUnit\Framework\TestCase;

class PrismCompilerTest extends TestCase
{
    private string $cachePath;
    private string $viewPath;

    protected function setUp(): void
    {
        $this->cachePath = sys_get_temp_dir() . '/prism_cache_' . uniqid();
        $this->viewPath = sys_get_temp_dir() . '/prism_views_' . uniqid();

        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }
        if (!is_dir($this->viewPath)) {
            mkdir($this->viewPath, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->cachePath);
        $this->removeDirectory($this->viewPath);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function test_compile_echo_tags(): void
    {
        $directives = $this->createMock(DirectiveRegistryContract::class);
        $compiler = new PrismCompiler($directives, $this->cachePath);

        $viewFile = $this->viewPath . '/test.prism.php';
        file_put_contents($viewFile, 'Hello {{ $name }}');

        $compiledPath = $compiler->compile($viewFile);
        $compiledContents = file_get_contents($compiledPath);

        $this->assertStringContainsString('<?php echo htmlspecialchars((string)($name), ENT_QUOTES, \'UTF-8\'); ?>', $compiledContents);
    }

    public function test_compile_raw_echo_tags(): void
    {
        $directives = $this->createMock(DirectiveRegistryContract::class);
        $compiler = new PrismCompiler($directives, $this->cachePath);

        $viewFile = $this->viewPath . '/test.prism.php';
        file_put_contents($viewFile, 'Hello {!! $html !!}');

        $compiledPath = $compiler->compile($viewFile);
        $compiledContents = file_get_contents($compiledPath);

        $this->assertStringContainsString('<?php echo $html; ?>', $compiledContents);
    }

    public function test_compile_self_closing_component(): void
    {
        $directives = $this->createMock(DirectiveRegistryContract::class);
        $compiler = new PrismCompiler($directives, $this->cachePath);

        $viewFile = $this->viewPath . '/test.prism.php';
        file_put_contents($viewFile, '<Button text="Click" variant="primary" />');

        $compiledPath = $compiler->compile($viewFile);
        $compiledContents = file_get_contents($compiledPath);

        $expected = "<?php echo \$__prism->component('Button', ['text' => 'Click', 'variant' => 'primary']); ?>";
        $this->assertStringContainsString($expected, $compiledContents);
    }

    public function test_compile_wrapping_component(): void
    {
        $directives = $this->createMock(DirectiveRegistryContract::class);
        $compiler = new PrismCompiler($directives, $this->cachePath);

        $viewFile = $this->viewPath . '/test.prism.php';
        file_put_contents($viewFile, '<Card title="Test">Card Content</Card>');

        $compiledPath = $compiler->compile($viewFile);
        $compiledContents = file_get_contents($compiledPath);

        $expected = "<?php echo \$__prism->component('Card', ['title' => 'Test'], 'Card Content'); ?>";
        $this->assertStringContainsString($expected, $compiledContents);
    }

    public function test_compile_directive(): void
    {
        $directives = $this->createMock(DirectiveRegistryContract::class);
        $mockDirective = $this->createMock(DirectiveContract::class);
        $mockDirective->method('compile')->with('$condition')->willReturn('<?php if($condition): ?>');
        $directives->method('get')->with('if')->willReturn($mockDirective);

        $compiler = new PrismCompiler($directives, $this->cachePath);

        $viewFile = $this->viewPath . '/test.prism.php';
        file_put_contents($viewFile, '#if($condition)');

        $compiledPath = $compiler->compile($viewFile);
        $compiledContents = file_get_contents($compiledPath);

        $this->assertStringContainsString('<?php if($condition): ?>', $compiledContents);
    }
}
