<?php

declare(strict_types=1);

namespace Horizon\Prism\Prism\Compiler;

use Horizon\Contracts\Prism\Compiler\DirectiveContract;
use Horizon\Contracts\Prism\Compiler\DirectiveRegistryContract;
use Horizon\Contracts\Prism\Compiler\PrismCompilerContract;
use InvalidArgumentException;

final readonly class PrismCompiler implements PrismCompilerContract
{

    public function __construct(
        private DirectiveRegistryContract $directives,
        private string                    $cachePath,
    ) {}

    public function compile(string $path): string
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException("File $path does not exist.");
        }

        $compiled = $this->compiledPath($path);

        if ($this->isExpired($path)) {
            $contents = file_get_contents($path);
            $result = $this->compileString($contents);

            if (!is_dir(dirname($compiled))) {
                mkdir(
                    directory: dirname($compiled),
                    permissions: 0755,
                    recursive: true
                );
            }

            file_put_contents($compiled, $result);
        }

        return $compiled;
    }

    public function isExpired(string $path): bool
    {
        $compiled = $this->compiledPath($path);

        if (!file_exists($compiled)) {
            return true;
        }

        return filemtime($path) > filemtime($compiled);
    }

    public function compiledPath(string $path): string
    {
        $hash = sha1($path);

        return rtrim($this->cachePath, '/\\') . '/' . $hash . '.php';
    }

    private function compileString(string $contents): string
    {
        $contents = $this->compileEchoTags($contents);
        $contents = $this->compileRawEchoTags($contents);
        $contents = $this->compileDirectives($contents);
        $contents = $this->compileComponents($contents);

        return $contents;
    }

    private function compileEchoTags(string $contents): string
    {
        // {{ $variable }} → <?php echo htmlspecialchars($variable, ENT_QUOTES)

        return preg_replace(
        '/\{\{\s*(.+?)\s*\}\}/',
        '<?php echo htmlspecialchars((string)($1), ENT_QUOTES, \'UTF-8\'); ?>',
            $contents
        );
    }

    private function compileRawEchoTags(string $contents): string
    {

        return preg_replace(
            '/\{!!\s*(.+?)\s*!!\}/',
            '<?php echo $1; ?>',
            $contents
        );
    }

    private function compileDirectives(string $contents): string
    {
        // #directive(expression) або #directive без виразу
        return preg_replace_callback(
            '/#(\w+)(?:\(([^)]*)\))?/',
            function (array $matches) {
                $name = $matches[1];
                $expression = $matches[2] ?? '';

                $handler = $this->directives->get($name);

                if ($handler === null) {
                    return $matches[0];
                }

                if ($handler instanceof DirectiveContract) {
                    return $handler->compile($expression);
                }

                return ($handler)($expression);
            },
            $contents
        );
    }

    private function compileComponents(string $contents): string
    {
        // <Button text="Click" /> → self-closing
        $contents = preg_replace_callback(
            '/<([A-Z][a-zA-Z]*)\s*([^>]*?)\/>/s',
            fn(array $m) => $this->compileSelfClosingComponent($m[1], $m[2]),
            $contents
        );

        // <Button text="Click">...</Button> → з контентом
        $contents = preg_replace_callback(
            '/<([A-Z][a-zA-Z]*)\s*([^>]*?)>(.*?)<\/\1>/s',
            fn(array $m) => $this->compileWrappingComponent($m[1], $m[2], $m[3]),
            $contents
        );

        return $contents;
    }

    private function compileSelfClosingComponent(string $name, string $attrs): string
    {
        $props = $this->parseAttributes($attrs);
        return "<?php echo \$__prism->component('$name', $props); ?>";
    }

    private function compileWrappingComponent(string $name, string $attrs, string $slot): string
    {
        $props = $this->parseAttributes($attrs);
        $slot = trim($slot);
        $escapedSlot = addslashes($slot);
        return "<?php echo \$__prism->component('$name', $props, '$escapedSlot'); ?>";
    }

    private function parseAttributes(string $attrs): string
    {
        // text="Click" variant="primary" → ['text' => 'Click', 'variant' => 'primary']
        preg_match_all('/(\w+)=["\']([^"\']*)["\']/', $attrs, $matches, PREG_SET_ORDER);

        $props = [];
        foreach ($matches as $match) {
            $props[] = "'$match[1]' => '$match[2]'";
        }

        return '[' . implode(', ', $props) . ']';
    }
}
