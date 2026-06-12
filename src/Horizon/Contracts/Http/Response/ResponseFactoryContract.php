<?php

declare(strict_types=1);

namespace Horizon\Contracts\Http\Response;

use Horizon\Contracts\Prism\ViewContract;

interface ResponseFactoryContract
{
    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function make(string $body = '', int $status = 200, array $headers = []): ResponseContract;

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function html(string $html = '', int $status = 200, array $headers = []): ResponseContract;

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function json(mixed $data = null, int $status = 200, array $headers = []): ResponseContract;

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function redirect(string $url, int $status = 302, array $headers = []): ResponseContract;

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function noContent(int $status = 204, array $headers = []): ResponseContract;

    /**
     * Render a Prism view and return it as an HTML response.
     *
     * @param  array<string, mixed>         $data
     * @param  array<string, scalar|null>   $headers
     */
    public function view(string $view, array $data = [], int $status = 200, array $headers = []): ResponseContract;
}
