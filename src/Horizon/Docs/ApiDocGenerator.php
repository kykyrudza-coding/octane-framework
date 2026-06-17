<?php

declare(strict_types=1);

namespace Horizon\Docs;

use FilesystemIterator;
use Reflection;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

final class ApiDocGenerator
{
    /**
     * @return list<class-string>
     */
    public function generate(string $sourcePath, string $outputPath): array
    {
        if (! is_dir($sourcePath)) {
            throw new RuntimeException("Source path [$sourcePath] does not exist.");
        }

        $classes = $this->discoverClasses($sourcePath);
        $this->prepareOutputDirectory($outputPath);

        $entries = [];
        foreach ($classes as $class) {
            if (! class_exists($class) && ! interface_exists($class) && ! trait_exists($class) && ! enum_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            if ($reflection->isInternal()) {
                continue;
            }

            $entries[] = $this->entry($reflection);
        }

        usort($entries, static fn (array $a, array $b): int => strcmp($a['class'], $b['class']));

        foreach ($entries as $entry) {
            $reflection = new ReflectionClass($entry['class']);
            $this->writeFile(
                $outputPath.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $entry['class']).'.html',
                $this->renderClass($reflection, $entry, $entries)
            );
        }

        foreach ($this->groupByModule($entries) as $module => $items) {
            $this->writeFile(
                $outputPath.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$this->slug($module).'.html',
                $this->renderModule($module, $items, $entries)
            );
        }

        $this->writeFile($outputPath.DIRECTORY_SEPARATOR.'providers.html', $this->renderProviders($entries));
        $this->writeFile($outputPath.DIRECTORY_SEPARATOR.'index.html', $this->renderIndex($entries));

        return array_map(static fn (array $entry): string => $entry['class'], $entries);
    }

    /**
     * @return list<class-string>
     */
    private function discoverClasses(string $sourcePath): array
    {
        $classes = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourcePath, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $class = $this->classFromFile($file->getPathname());
            if ($class !== null) {
                $classes[] = $class;
            }
        }

        sort($classes);

        return $classes;
    }

    /**
     * @return class-string|null
     */
    private function classFromFile(string $file): ?string
    {
        $tokens = token_get_all((string) file_get_contents($file));
        $namespace = '';

        for ($i = 0, $count = count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];

            if (is_array($token) && $token[0] === T_NAMESPACE) {
                $namespace = $this->readNamespace($tokens, $i + 1);
            }

            if (is_array($token) && in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM], true)) {
                if ($this->previousMeaningfulTokenIsNew($tokens, $i)) {
                    continue;
                }

                $name = $this->readClassName($tokens, $i + 1);
                if ($name === null) {
                    return null;
                }

                return $namespace === '' ? $name : $namespace.'\\'.$name;
            }
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $tokens
     */
    private function readNamespace(array $tokens, int $offset): string
    {
        $namespace = '';

        for ($i = $offset, $count = count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];

            if ($token === ';' || $token === '{') {
                break;
            }

            if (is_array($token) && in_array($token[0], [T_STRING, T_NAME_QUALIFIED, T_NS_SEPARATOR], true)) {
                $namespace .= $token[1];
            }
        }

        return $namespace;
    }

    /**
     * @param  array<int, mixed>  $tokens
     */
    private function readClassName(array $tokens, int $offset): ?string
    {
        for ($i = $offset, $count = count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];

            if (is_array($token) && $token[0] === T_STRING) {
                return $token[1];
            }
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $tokens
     */
    private function previousMeaningfulTokenIsNew(array $tokens, int $offset): bool
    {
        for ($i = $offset - 1; $i >= 0; $i--) {
            $token = $tokens[$i];

            if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            return is_array($token) && $token[0] === T_NEW;
        }

        return false;
    }

    /**
     * @return array{class: class-string, short: string, kind: string, namespace: string, module: string, path: string, provider: bool, contract: bool}
     */
    private function entry(ReflectionClass $class): array
    {
        return [
            'class' => $class->getName(),
            'short' => $class->getShortName(),
            'kind' => $this->kind($class),
            'namespace' => $class->getNamespaceName(),
            'module' => $this->moduleName($class->getName()),
            'path' => 'classes/'.str_replace('\\', '/', $class->getName()).'.html',
            'provider' => $class->isSubclassOf(\Horizon\Support\Providers\ServiceProvider::class),
            'contract' => str_contains($class->getNamespaceName(), '\\Contracts\\'),
        ];
    }

    private function renderIndex(array $entries): string
    {
        $modules = $this->groupByModule($entries);
        $providers = array_values(array_filter($entries, static fn (array $entry): bool => $entry['provider']));
        $contracts = array_values(array_filter($entries, static fn (array $entry): bool => $entry['contract']));

        $content = $this->hero(
            'Octane API Reference',
            'Framework modules',
            count($entries).' symbols across '.count($modules).' modules. Generated from the installed Horizon source.'
        );

        $content .= '<section class="metric-grid">';
        $content .= $this->metric('Symbols', (string) count($entries));
        $content .= $this->metric('Modules', (string) count($modules));
        $content .= $this->metric('Providers', (string) count($providers));
        $content .= $this->metric('Contracts', (string) count($contracts));
        $content .= '</section>';

        $content .= '<section class="panel"><div class="section-head"><h2>Packages</h2><p>Grouped by the first namespace segment under Horizon.</p></div>';
        $content .= '<div class="module-grid">';

        foreach ($modules as $module => $items) {
            $contractsInModule = count(array_filter($items, static fn (array $entry): bool => $entry['contract']));
            $providersInModule = count(array_filter($items, static fn (array $entry): bool => $entry['provider']));
            $content .= '<a class="module-card" href="/_octane/api/modules/'.$this->slug($module).'.html">';
            $content .= '<span class="eyebrow">Package</span>';
            $content .= '<strong>'.$this->escape($module).'</strong>';
            $content .= '<span>'.$this->escape((string) count($items)).' symbols</span>';
            $content .= '<small>'.$contractsInModule.' contracts / '.$providersInModule.' providers</small>';
            $content .= '</a>';
        }

        $content .= '</div></section>';
        $content .= $this->providerPreview($providers);

        return $this->layout('Octane API', $this->sidebar($entries, 'index'), $content);
    }

    private function renderModule(string $module, array $items, array $entries): string
    {
        $namespaces = [];
        foreach ($items as $entry) {
            $namespaces[$entry['namespace']][] = $entry;
        }

        $content = $this->hero('Package', $module, count($items).' symbols in the '.$module.' module.');
        $content .= '<section class="symbol-table panel">';
        $content .= '<div class="section-head"><h2>Symbols</h2><p>Classes, contracts, traits and value objects inside this package.</p></div>';

        foreach ($namespaces as $namespace => $symbols) {
            $content .= '<h3>'.$this->escape($namespace).'</h3>';
            $content .= '<div class="row-list">';
            foreach ($symbols as $entry) {
                $content .= $this->symbolRow($entry);
            }
            $content .= '</div>';
        }

        $content .= '</section>';

        return $this->layout($module, $this->sidebar($entries, $module), $content);
    }

    private function renderProviders(array $entries): string
    {
        $providers = array_values(array_filter($entries, static fn (array $entry): bool => $entry['provider']));

        $content = $this->hero(
            'Framework bootstrap',
            'Service providers',
            'Providers discovered from the Horizon source. Use this page to inspect framework registration boundaries.'
        );

        $content .= '<section class="panel">';
        $content .= '<div class="section-head"><h2>Registered provider classes</h2><p>Provider pages include register and boot methods when present.</p></div>';
        $content .= '<div class="row-list">';

        foreach ($providers as $provider) {
            $content .= $this->symbolRow($provider);
        }

        $content .= '</div></section>';

        return $this->layout('Providers', $this->sidebar($entries, 'providers'), $content);
    }

    private function renderClass(ReflectionClass $class, array $entry, array $entries): string
    {
        $methods = $this->methods($class);
        $properties = $this->properties($class);
        $constants = $this->constants($class);

        $content = $this->hero($entry['kind'], $class->getName(), $this->classSubtitle($class));
        $content .= $this->classSummary($class, $entry, count($constants), count($properties), count($methods));
        $content .= $this->usageExamples($class, $entry);

        if ($constants !== []) {
            $content .= '<section id="constants" class="panel"><div class="section-head"><h2>Constants</h2><p>Class constants declared or inherited by this symbol.</p></div>';
            $content .= '<div class="member-list">';
            foreach ($constants as $constant) {
                $content .= $this->renderConstant($class, $constant);
            }
            $content .= '</div></section>';
        }

        if ($properties !== []) {
            $content .= '<section id="properties" class="panel"><div class="section-head"><h2>Properties</h2><p>Public, protected and private properties visible through reflection.</p></div>';
            $content .= '<div class="member-list">';
            foreach ($properties as $property) {
                $content .= $this->renderProperty($class, $property);
            }
            $content .= '</div></section>';
        }

        $content .= '<section id="methods" class="panel">';
        $content .= '<div class="section-head"><h2>Methods</h2><p>Public and protected callable surface. Inherited members are marked.</p></div>';

        if ($methods === []) {
            $content .= '<div class="empty-state">No public or protected methods.</div>';
        } else {
            $content .= '<div class="method-list">';
            foreach ($methods as $method) {
                $content .= $this->renderMethod($class, $method);
            }
            $content .= '</div>';
        }

        $content .= '</section>';

        return $this->layout($class->getShortName(), $this->classSidebar($entries, $class, $constants, $properties, $methods), $content);
    }

    /**
     * @return list<ReflectionMethod>
     */
    private function methods(ReflectionClass $class): array
    {
        $methods = array_values(array_filter(
            $class->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED),
            static fn (ReflectionMethod $method): bool => ! $method->isConstructor() && ! $method->isDestructor()
        ));

        usort($methods, static function (ReflectionMethod $a, ReflectionMethod $b): int {
            return [$a->isPublic() ? 0 : 1, $a->getName()] <=> [$b->isPublic() ? 0 : 1, $b->getName()];
        });

        return $methods;
    }

    /**
     * @return list<ReflectionProperty>
     */
    private function properties(ReflectionClass $class): array
    {
        $properties = $class->getProperties();

        usort($properties, static function (ReflectionProperty $a, ReflectionProperty $b): int {
            return [$a->isPublic() ? 0 : ($a->isProtected() ? 1 : 2), $a->getName()]
                <=> [$b->isPublic() ? 0 : ($b->isProtected() ? 1 : 2), $b->getName()];
        });

        return $properties;
    }

    /**
     * @return list<ReflectionClassConstant>
     */
    private function constants(ReflectionClass $class): array
    {
        $constants = $class->getReflectionConstants();

        usort($constants, static function (ReflectionClassConstant $a, ReflectionClassConstant $b): int {
            return [$a->isPublic() ? 0 : ($a->isProtected() ? 1 : 2), $a->getName()]
                <=> [$b->isPublic() ? 0 : ($b->isProtected() ? 1 : 2), $b->getName()];
        });

        return $constants;
    }

    private function renderConstant(ReflectionClass $owner, ReflectionClassConstant $constant): string
    {
        $signature = trim(implode(' ', Reflection::getModifierNames($constant->getModifiers())).' const '.$constant->getName().' = '.$this->constantValue($constant));
        $html = '<article class="member-card">';
        $html .= '<div class="member-head"><h3>'.$this->escape($constant->getName()).'</h3><span>'.$this->visibility($constant).'</span></div>';
        $html .= '<pre><code>'.$this->escape($signature).'</code></pre>';
        $html .= $this->declaredIn($owner->getName(), $constant->getDeclaringClass()->getName());
        $html .= '</article>';

        return $html;
    }

    private function renderProperty(ReflectionClass $owner, ReflectionProperty $property): string
    {
        $modifiers = Reflection::getModifierNames($property->getModifiers());
        $type = $property->hasType() ? $this->typeToString($property->getType()).' ' : '';
        $signature = trim(implode(' ', $modifiers).' '.$type.'$'.$property->getName());

        $html = '<article class="member-card">';
        $html .= '<div class="member-head"><h3>$'.$this->escape($property->getName()).'</h3><span>'.$this->visibility($property).'</span></div>';
        $html .= '<pre><code>'.$this->escape($signature).'</code></pre>';
        $html .= $this->declaredIn($owner->getName(), $property->getDeclaringClass()->getName());
        $html .= '</article>';

        return $html;
    }

    private function renderMethod(ReflectionClass $owner, ReflectionMethod $method): string
    {
        $modifiers = implode(' ', Reflection::getModifierNames($method->getModifiers()));
        $signature = trim($modifiers.' function '.$method->getName().'('.implode(', ', array_map(
            fn (ReflectionParameter $parameter): string => $this->parameterSignature($parameter),
            $method->getParameters()
        )).')'.$this->returnType($method));

        $html = '<article id="method-'.$this->slug($method->getName()).'" class="method-card">';
        $html .= '<div class="member-head"><h3>'.$this->escape($method->getName()).'</h3><span>'.$this->visibility($method).'</span></div>';
        $html .= '<pre><code>'.$this->escape($signature).'</code></pre>';
        $html .= $this->declaredIn($owner->getName(), $method->getDeclaringClass()->getName());
        $html .= '</article>';

        return $html;
    }

    private function classSummary(ReflectionClass $class, array $entry, int $constants, int $properties, int $methods): string
    {
        $content = '<section class="metric-grid">';
        $content .= $this->metric('Module', $entry['module']);
        $content .= $this->metric('Kind', $entry['kind']);
        $content .= $this->metric('Constants', (string) $constants);
        $content .= $this->metric('Properties', (string) $properties);
        $content .= $this->metric('Methods', (string) $methods);
        $content .= '</section>';

        $details = [
            ['Namespace', $class->getNamespaceName()],
            ['File', $class->getFileName() ?: 'unknown'],
        ];

        if ($class->getParentClass() !== false) {
            $details[] = ['Extends', $class->getParentClass()->getName()];
        }

        $interfaces = $class->getInterfaceNames();
        if ($interfaces !== []) {
            $details[] = ['Implements', implode(', ', $interfaces)];
        }

        $content .= '<section class="panel"><div class="section-head"><h2>Overview</h2><p>Static metadata for this symbol.</p></div><div class="meta-table">';
        foreach ($details as [$label, $value]) {
            $content .= '<div><span>'.$this->escape($label).'</span><code>'.$this->escape($value).'</code></div>';
        }
        $content .= '</div></section>';

        return $content;
    }

    private function usageExamples(ReflectionClass $class, array $entry): string
    {
        $examples = [];

        if ($entry['provider']) {
            $examples[] = [
                'Register provider',
                '$app->registerProvider(new '.$class->getShortName().'($app));',
            ];
        }

        if (str_ends_with($class->getName(), 'Facade')) {
            $examples[] = [
                'Facade call',
                $class->getShortName().'::method(...);',
            ];
        }

        if ($class->getShortName() === 'QueryBuilder') {
            $examples[] = [
                'Query rows',
                "QueryBuilder::table('users')->where('active', 1)->get();",
            ];
        }

        if ($class->isInstantiable() && $class->getConstructor() === null) {
            $examples[] = [
                'Resolve from container',
                '$instance = app('.$class->getShortName().'::class);',
            ];
        }

        if ($examples === []) {
            return '';
        }

        $html = '<section class="panel"><div class="section-head"><h2>Usage</h2><p>Short examples generated from the symbol role.</p></div><div class="example-grid">';
        foreach ($examples as [$title, $code]) {
            $html .= '<article class="example-card"><h3>'.$this->escape($title).'</h3><pre><code>'.$this->escape($code).'</code></pre></article>';
        }
        $html .= '</div></section>';

        return $html;
    }

    private function providerPreview(array $providers): string
    {
        $html = '<section class="panel"><div class="section-head"><h2>Providers</h2><p>Framework service registration boundaries.</p><a href="/_octane/api/providers.html">View all</a></div><div class="row-list">';

        foreach (array_slice($providers, 0, 8) as $provider) {
            $html .= $this->symbolRow($provider);
        }

        $html .= '</div></section>';

        return $html;
    }

    private function symbolRow(array $entry): string
    {
        return '<a class="symbol-row" href="/_octane/api/'.$this->escape($entry['path']).'">'
            .'<span>'.$this->escape($entry['kind']).'</span>'
            .'<strong>'.$this->escape($entry['class']).'</strong>'
            .'<small>'.$this->escape($entry['module']).'</small>'
            .'</a>';
    }

    private function metric(string $label, string $value): string
    {
        return '<div class="metric"><span>'.$this->escape($label).'</span><strong>'.$this->escape($value).'</strong></div>';
    }

    private function hero(string $eyebrow, string $title, string $description): string
    {
        return '<div class="hero"><span>'.$this->escape($eyebrow).'</span><h1>'.$this->escape($title).'</h1><p>'.$this->escape($description).'</p></div>';
    }

    private function sidebar(array $entries, string $active): string
    {
        $modules = array_keys($this->groupByModule($entries));
        $html = '<a class="'.($active === 'index' ? 'active' : '').'" href="/_octane/api">◈ Overview</a>';
        $html .= '<a class="'.($active === 'providers' ? 'active' : '').'" href="/_octane/api/providers.html">◆ Providers</a>';
        $html .= '<div class="nav-label">Packages</div>';

        foreach ($modules as $module) {
            $html .= '<a class="'.($active === $module ? 'active' : '').'" href="/_octane/api/modules/'.$this->slug($module).'.html">'.$this->escape($module).'</a>';
        }

        return $html;
    }

    private function classSidebar(array $entries, ReflectionClass $class, array $constants, array $properties, array $methods): string
    {
        $html = $this->sidebar($entries, $this->moduleName($class->getName()));
        $html .= '<div class="nav-label">On this page</div>';
        $html .= '<a href="#top">◈ Overview</a>';

        if ($constants !== []) {
            $html .= '<a href="#constants"># Constants</a>';
        }

        if ($properties !== []) {
            $html .= '<a href="#properties">$ Properties</a>';
        }

        $html .= '<a href="#methods">ƒ Methods</a>';

        foreach (array_slice($methods, 0, 24) as $method) {
            $html .= '<a class="sub" href="#method-'.$this->slug($method->getName()).'">› '.$this->escape($method->getName()).'</a>';
        }

        return $html;
    }

    private function layout(string $title, string $sidebar, string $content): string
    {
        return '<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="'.$this->escape($title).' — Octane Framework API Reference. Auto-generated from source.">
    <meta name="theme-color" content="#f8f9fb">
    <title>'.$this->escape($title).' — Octane API</title>
    '.$this->style().'
</head>
<body id="top">
    <aside class="sidebar">
        <div class="brand"><span>⚡ Octane</span><strong>API Reference</strong></div>
        <nav>'.$sidebar.'</nav>
    </aside>
    <main class="content">'.$content.'
        <footer style="margin-top:48px;padding-top:20px;border-top:1px solid var(--border);color:var(--muted);font-size:12px;display:flex;justify-content:space-between;align-items:center;">
            <span>Octane Framework — Auto-generated API documentation</span>
            <span>'.date('Y-m-d H:i').'</span>
        </footer>
    </main>
</body>
</html>';
    }


    private function style(): string
    {
        return '<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
    --bg: #f8f9fb;
    --surface: #ffffff;
    --surface-alt: #f3f4f8;
    --border: #e2e5ec;
    --border-focus: #c7cad4;
    --text: #1a1d27;
    --text-secondary: #555b6e;
    --muted: #8790a3;
    --accent: #4f58e0;
    --accent-bg: #eef0ff;
    --code-bg: #f5f6fa;
    --sidebar-w: 260px;
    --radius: 8px;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
    min-height: 100vh;
    background: var(--bg);
    color: var(--text);
    font: 400 14px/1.6 "Inter", system-ui, sans-serif;
    -webkit-font-smoothing: antialiased;
}
.sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    width: var(--sidebar-w);
    overflow-y: auto;
    background: var(--surface);
    border-right: 1px solid var(--border);
}
.brand {
    padding: 20px;
    border-bottom: 1px solid var(--border);
}
.brand span {
    display: block;
    margin-bottom: 4px;
    color: var(--accent);
    font: 600 10px/1 "JetBrains Mono", monospace;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}
.brand strong { font-size: 15px; font-weight: 700; color: var(--text); }
.eyebrow {
    display: inline-block;
    margin-bottom: 8px;
    padding: 3px 10px;
    border-radius: 4px;
    background: var(--accent-bg);
    color: var(--accent);
    font: 600 10px/1.4 "JetBrains Mono", monospace;
    text-transform: uppercase;
    letter-spacing: 1.2px;
}
nav { padding: 10px 8px; }
nav a {
    display: block;
    padding: 6px 12px;
    color: var(--text-secondary);
    text-decoration: none;
    border-radius: 6px;
    font-size: 13px;
    line-height: 1.4;
    overflow-wrap: anywhere;
}
nav a.sub { padding-left: 26px; font-size: 12px; color: var(--muted); }
nav a:hover { background: var(--surface-alt); color: var(--text); }
nav a.active { background: var(--accent-bg); color: var(--accent); font-weight: 600; }
.nav-label {
    margin: 16px 12px 6px;
    color: var(--muted);
    font: 600 10px/1 "JetBrains Mono", monospace;
    text-transform: uppercase;
    letter-spacing: 1.2px;
}
.content {
    margin-left: var(--sidebar-w);
    padding: 32px 40px 60px;
    max-width: 1200px;
}
.hero {
    padding-bottom: 20px;
    margin-bottom: 24px;
    border-bottom: 1px solid var(--border);
}
.hero span {
    display: inline-block;
    margin-bottom: 8px;
    padding: 3px 10px;
    border-radius: 4px;
    background: var(--accent-bg);
    color: var(--accent);
    font: 600 10px/1.4 "JetBrains Mono", monospace;
    text-transform: uppercase;
    letter-spacing: 1.2px;
}
h1 {
    margin: 0 0 6px;
    font-size: 28px;
    font-weight: 700;
    line-height: 1.2;
    letter-spacing: -0.4px;
    color: var(--text);
}
.hero p { color: var(--text-secondary); font-size: 14px; }
h2 { font-size: 15px; font-weight: 700; color: var(--text); }
h3 { font-size: 13px; font-weight: 600; color: var(--text); }
p { color: var(--text-secondary); }
.muted { color: var(--muted); font-size: 13px; margin-top: 6px; }
.panel {
    margin-bottom: 16px;
    padding: 18px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
}
.section-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}
.section-head p { margin: 3px 0 0; font-size: 13px; color: var(--muted); }
.section-head a { color: var(--accent); text-decoration: none; font-size: 13px; font-weight: 500; }
.section-head a:hover { text-decoration: underline; }
.metric-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}
.metric {
    padding: 14px 16px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
}
.metric span, .symbol-row span, .member-head span {
    color: var(--muted);
    font: 500 10px/1 "JetBrains Mono", monospace;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}
.metric strong {
    display: block;
    margin-top: 6px;
    font-size: 20px;
    font-weight: 700;
    overflow-wrap: anywhere;
}
.module-grid, .example-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 10px;
}
.module-card, .example-card {
    padding: 16px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
}
.module-card { color: var(--text); text-decoration: none; }
.module-card:hover { border-color: var(--border-focus); }
.module-card strong { display: block; margin: 6px 0 4px; font-size: 15px; font-weight: 700; }
.module-card span:not(.eyebrow), .module-card small { display: block; color: var(--muted); font-size: 13px; }
.row-list, .member-list, .method-list { display: grid; gap: 6px; }
.symbol-row {
    display: grid;
    grid-template-columns: 100px minmax(0, 1fr) 120px;
    gap: 12px;
    align-items: center;
    padding: 10px 14px;
    color: var(--text);
    text-decoration: none;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
}
.symbol-row:hover { border-color: var(--border-focus); }
.symbol-row strong, .symbol-row small { overflow-wrap: anywhere; }
.symbol-row strong { font: 500 13px/1.4 "JetBrains Mono", monospace; color: var(--text); }
.symbol-row small { color: var(--muted); font-size: 12px; }
.symbol-row span {
    padding: 2px 8px;
    border-radius: 4px;
    background: var(--accent-bg);
    color: var(--accent) !important;
    font-size: 10px !important;
    text-align: center;
}
.meta-table {
    display: grid;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}
.meta-table div {
    display: grid;
    grid-template-columns: 130px minmax(0, 1fr);
    gap: 12px;
    align-items: start;
    padding: 10px 14px;
    border-bottom: 1px solid var(--border);
}
.meta-table div:last-child { border-bottom: 0; }
.meta-table span { color: var(--muted); font-weight: 500; font-size: 13px; }
.member-card, .method-card {
    padding: 14px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--surface);
}
.member-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.member-head h3 { color: var(--accent); }
.member-head span {
    padding: 2px 8px;
    border-radius: 4px;
    background: var(--accent-bg);
    font-size: 10px !important;
}
pre {
    margin: 10px 0 0;
    padding: 12px 14px;
    overflow-x: auto;
    border-radius: 6px;
    background: var(--code-bg);
    border: 1px solid var(--border);
}
code { font: 13px/1.6 "JetBrains Mono", monospace; color: #2d3250; }
.example-card h3 { color: var(--accent); margin-bottom: 2px; }
.empty-state {
    padding: 20px;
    color: var(--muted);
    border: 1px dashed var(--border-focus);
    border-radius: var(--radius);
    text-align: center;
    font-size: 13px;
}
@media (max-width: 900px) {
    .sidebar { position: static; width: auto; max-height: 260px; border-right: none; border-bottom: 1px solid var(--border); }
    .content { margin-left: 0; padding: 20px 16px 40px; }
    .symbol-row, .meta-table div { grid-template-columns: 1fr; }
    .metric-grid { grid-template-columns: repeat(2, 1fr); }
    .module-grid, .example-grid { grid-template-columns: 1fr; }
}
</style>';
    }
    private function parameterSignature(ReflectionParameter $parameter): string
    {
        $signature = '';

        if ($parameter->hasType()) {
            $signature .= $this->typeToString($parameter->getType()).' ';
        }

        if ($parameter->isPassedByReference()) {
            $signature .= '&';
        }

        if ($parameter->isVariadic()) {
            $signature .= '...';
        }

        $signature .= '$'.$parameter->getName();

        if ($parameter->isDefaultValueAvailable() && ! $parameter->isVariadic()) {
            $signature .= ' = '.$this->defaultValue($parameter);
        }

        return $signature;
    }

    private function returnType(ReflectionMethod $method): string
    {
        if (! $method->hasReturnType()) {
            return '';
        }

        return ': '.$this->typeToString($method->getReturnType());
    }

    private function typeToString(?ReflectionType $type): string
    {
        if ($type === null) {
            return '';
        }

        if ($type instanceof ReflectionNamedType) {
            return ($type->allowsNull() && $type->getName() !== 'mixed' ? '?' : '').$type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            return implode('|', array_map(fn (ReflectionType $type): string => $this->typeToString($type), $type->getTypes()));
        }

        if ($type instanceof ReflectionIntersectionType) {
            return implode('&', array_map(fn (ReflectionType $type): string => $this->typeToString($type), $type->getTypes()));
        }

        return (string) $type;
    }

    private function defaultValue(ReflectionParameter $parameter): string
    {
        if ($parameter->isDefaultValueConstant()) {
            return (string) $parameter->getDefaultValueConstantName();
        }

        return var_export($parameter->getDefaultValue(), true);
    }

    private function constantValue(ReflectionClassConstant $constant): string
    {
        $value = $constant->getValue();

        if (is_array($value)) {
            return 'array';
        }

        return var_export($value, true);
    }

    private function classSubtitle(ReflectionClass $class): string
    {
        $file = $class->getFileName();

        return $file === false ? 'Runtime symbol.' : $file;
    }

    private function declaredIn(string $owner, string $declaring): string
    {
        if ($owner === $declaring) {
            return '';
        }

        return '<p class="muted" style="display:flex;align-items:center;gap:6px;"><span style="display:inline-block;padding:2px 8px;border-radius:4px;background:var(--accent-bg);font-size:10px;color:var(--accent);font-family:JetBrains Mono,monospace;letter-spacing:0.5px;">INHERITED</span> from '.$this->escape($declaring).'</p>';
    }

    private function visibility(ReflectionMethod|ReflectionProperty|ReflectionClassConstant $reflection): string
    {
        return match (true) {
            $reflection->isPublic() => 'public',
            $reflection->isProtected() => 'protected',
            default => 'private',
        };
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function groupByModule(array $entries): array
    {
        $groups = [];

        foreach ($entries as $entry) {
            $groups[$entry['module']][] = $entry;
        }

        ksort($groups);

        return $groups;
    }

    private function moduleName(string $class): string
    {
        $parts = explode('\\', $class);

        return $parts[1] ?? 'Core';
    }

    private function prepareOutputDirectory(string $outputPath): void
    {
        if (! is_dir($outputPath) && ! mkdir($outputPath, 0775, true) && ! is_dir($outputPath)) {
            throw new RuntimeException("Unable to create output path [$outputPath].");
        }
    }

    private function writeFile(string $path, string $contents): void
    {
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create directory [$directory].");
        }

        file_put_contents($path, $contents);
    }

    private function kind(ReflectionClass $class): string
    {
        return match (true) {
            $class->isInterface() => 'interface',
            $class->isTrait() => 'trait',
            $class->isEnum() => 'enum',
            $class->isAbstract() => 'abstract class',
            default => 'class',
        };
    }

    private function slug(string $value): string
    {
        return strtolower(trim((string) preg_replace('/[^A-Za-z0-9_]+/', '-', $value), '-'));
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
