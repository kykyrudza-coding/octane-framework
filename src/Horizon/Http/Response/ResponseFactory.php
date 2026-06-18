<?php

declare(strict_types=1);

namespace Horizon\Http\Response;

use Horizon\Arch\Application;
use Horizon\Arch\Exceptions\BindingResolutionException;
use Horizon\Contracts\Http\Response\ResponseContract;
use Horizon\Contracts\Http\Response\ResponseFactoryContract;
use Horizon\Contracts\Prism\ViewFactoryContract;

class ResponseFactory implements ResponseFactoryContract
{
    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function make(string $body = '', int $status = 200, array $headers = []): ResponseContract
    {
        return new Response($body, $status, $headers);
    }

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function html(string $html = '', int $status = 200, array $headers = []): ResponseContract
    {
        return new Response($html, $status, $headers);
    }

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function json(mixed $data = null, int $status = 200, array $headers = []): ResponseContract
    {
        return new JsonResponse($data, $status, $headers);
    }

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function redirect(string $url, int $status = 302, array $headers = []): ResponseContract
    {
        return new RedirectResponse($url, $status, $headers);
    }

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function noContent(int $status = 204, array $headers = []): ResponseContract
    {
        return new Response('', $status, $headers);
    }

    /**
     * @param  array<string, mixed>        $data
     * @param  array<string, scalar|null>  $headers
     */
    public function view(string $view, array $data = [], int $status = 200, array $headers = []): ResponseContract
    {
        $factory = Application::getInstance()->make(ViewFactoryContract::class);

        if (! $factory instanceof ViewFactoryContract) {
            throw new BindingResolutionException(
                'View factory binding must resolve to a ViewFactoryContract instance.'
            );
        }

        $html = $factory->make($view, $data)->render();

        $headers['Content-Type'] ??= 'text/html; charset=UTF-8';

        return new Response($html, $status, $headers);
    }
}
