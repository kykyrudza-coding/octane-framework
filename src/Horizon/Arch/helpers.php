<?php

declare(strict_types=1);

use Horizon\Arch\Application;
use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Contracts\Arch\Container\ContainerContract;
use Horizon\Contracts\Http\Request\RequestContract;
use Horizon\Contracts\Http\Response\ResponseContract;
use Horizon\Contracts\Http\Response\ResponseFactoryContract;
use Horizon\Contracts\Prism\ViewContract;
use Horizon\Contracts\Prism\ViewFactoryContract;
use Horizon\Http\Exceptions\HttpException;
use Horizon\Http\Response\RedirectResponse;

if (! function_exists('app')) {
    /**
     * Get the available application instance or resolve a binding.
     */
    function app(?string $abstract = null): mixed
    {
        if ($abstract === null) {
            return Application::getInstance();
        }

        return Application::getInstance()->make($abstract);
    }
}

if (! function_exists('container')) {
    /**
     * Get the available container instance.
     */
    function container(): ContainerContract
    {
        return Application::getInstance()->getContainer();
    }
}

if (! function_exists('config')) {
    /**
     * Get a configuration value.
     */
    function config(string $key, mixed $default = null): mixed
    {
        $repository = Application::getInstance()->make(ConfigRepositoryContract::class);

        if (! $repository instanceof ConfigRepositoryContract) {
            throw new RuntimeException('Config repository binding must resolve to a ConfigRepositoryContract instance.');
        }

        return $repository->get($key, $default);
    }
}

if (! function_exists('env')) {
    /**
     * Get an environment variable with proper boolean/null casting.
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? null;

        if ($value === null) {
            $value = $_SERVER[$key] ?? null;
        }

        if ($value === null) {
            $environmentValue = getenv($key);
            $value = $environmentValue === false ? null : $environmentValue;
        }

        if (! is_scalar($value)) {
            return $default;
        }

        $value = (string) $value;

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $value,
        };
    }
}

if (! function_exists('response')) {
    /**
     * Return a new response instance.
     *
     * @param  array<string, scalar|null>  $headers
     * @return ($body is null ? ResponseFactoryContract : ResponseContract)
     */
    function response(?string $body = null, int $status = 200, array $headers = []): ResponseContract|ResponseFactoryContract
    {
        $factory = Application::getInstance()->make(ResponseFactoryContract::class);

        if (! $factory instanceof ResponseFactoryContract) {
            throw new RuntimeException('Response factory binding must resolve to a ResponseFactoryContract instance.');
        }

        if ($body === null) {
            return $factory;
        }

        return $factory->make($body, $status, $headers);
    }
}

if (! function_exists('redirect')) {
    /**
     * Return a new redirect response.
     *
     * @param  array<string, scalar|null>  $headers
     */
    function redirect(string $url, int $status = 302, array $headers = []): RedirectResponse
    {
        $factory = Application::getInstance()->make(ResponseFactoryContract::class);
        if (! $factory instanceof ResponseFactoryContract) {
            throw new RuntimeException('Response factory binding must resolve to a ResponseFactoryContract instance.');
        }

        $response = $factory->redirect($url, $status, $headers);
        if (! $response instanceof RedirectResponse) {
            throw new RuntimeException('Response factory redirect must return a RedirectResponse instance.');
        }

        return $response;
    }
}

if (! function_exists('request')) {
    /**
     * Get the current request instance.
     */
    function request(): RequestContract
    {
        $request = app(RequestContract::class);

        if (! $request instanceof RequestContract) {
            throw new RuntimeException('Request binding must resolve to a RequestContract instance.');
        }

        return $request;
    }
}

if (! function_exists('abort')) {
    /**
     * Throw an HttpException with the given data.
     *
     * @param  array<string, scalar|null>  $headers
     */
    function abort(int $code, string $message = '', array $headers = []): never
    {
        throw new HttpException($code, $message, null, $headers);
    }
}

if (! function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view or the view factory.
     *
     * @param  array<string, mixed>  $data
     */
    function view(?string $view = null, array $data = []): ViewContract|ViewFactoryContract
    {
        $factory = Application::getInstance()->make(ViewFactoryContract::class);

        if (! $factory instanceof ViewFactoryContract) {
            throw new RuntimeException('View factory binding must resolve to a ViewFactoryContract instance.');
        }

        if ($view === null) {
            return $factory;
        }

        return $factory->make($view, $data);
    }
}

if (! function_exists('vite')) {
    /**
     * Get the HTML tags for Vite assets.
     */
    function vite(string $entry): string
    {
        // Check if Vite dev server is running on port 5173
        $devServerRunning = false;
        $connection = @fsockopen('127.0.0.1', 5173, $errno, $errstr, 0.03);
        if ($connection) {
            $devServerRunning = true;
            fclose($connection);
        }

        if (!$devServerRunning) {
            $basePath = Application::getInstance()->basePath();
            $manifestPath = $basePath . '/public/build/.vite/manifest.json';

            if (! file_exists($manifestPath)) {
                $manifestPath = $basePath . '/public/build/manifest.json';
            }

            if (file_exists($manifestPath)) {
                $manifest = json_decode((string) file_get_contents($manifestPath), true);
                if (isset($manifest[$entry])) {
                    $file = $manifest[$entry]['file'];
                    $html = '<script type="module" src="/build/' . $file . '"></script>';
                    if (isset($manifest[$entry]['css'])) {
                        foreach ($manifest[$entry]['css'] as $css) {
                            $html .= "\n" . '<link rel="stylesheet" href="/build/' . $css . '">';
                        }
                    }
                    return $html;
                }
            }
        }

        // Dev server fallback
        return '
            <script type="module" src="http://127.0.0.1:5173/@vite/client"></script>
            <script type="module" src="http://127.0.0.1:5173/' . ltrim($entry, '/') . '"></script>
        ';
    }
}


