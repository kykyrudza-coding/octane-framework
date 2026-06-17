<?php

declare(strict_types=1);

namespace Horizon\Docs\Controllers;

use Horizon\Arch\Application;
use Horizon\Http\Response\Response;

final class ApiDocsController
{
    public function index(): Response
    {
        return $this->show('index.html');
    }

    public function show(string $path = 'index.html'): Response
    {
        $path = $path === '' ? 'index.html' : $path;

        if ($this->isUnsafePath($path)) {
            return new Response('Not found.', 404);
        }

        $app = Application::getInstance();
        $base = $app->varPath('framework/api-docs');
        $baseRealPath = realpath($base);

        if ($baseRealPath === false) {
            return $this->missingDocumentationResponse();
        }

        $target = realpath($base.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));

        if ($target === false || ! str_starts_with($target, $baseRealPath) || ! is_file($target)) {
            return new Response('Not found.', 404);
        }

        return new Response(
            (string) file_get_contents($target),
            200,
            ['Content-Type' => $this->contentType($target)]
        );
    }

    private function missingDocumentationResponse(): Response
    {
        return new Response(
            '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Octane API</title></head><body><h1>Octane API docs are not generated.</h1><p>Run <code>php octane docs:api</code>.</p></body></html>',
            404
        );
    }

    private function isUnsafePath(string $path): bool
    {
        return str_contains($path, '..')
            || str_starts_with($path, '/')
            || preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1;
    }

    private function contentType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'text/html; charset=UTF-8',
        };
    }
}
