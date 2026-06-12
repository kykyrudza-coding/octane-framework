<?php

declare(strict_types=1);

namespace Horizon\Arch\Traits;

/**
 * @requires Application::instance(string $abstract, object $instance): void
 */
trait ManagesApplicationPaths
{
    protected string $_appPath;

    protected string $_publicPath;

    protected string $_varPath;

    protected string $_configPath;

    protected string $_dbPath;

    protected string $_cachePath;

    protected string $_logPath;

    protected string $_uiPath;

    /** Getters */
    public function appPath(string $path = ''): string
    {
        return $this->joinPaths($this->_appPath ?? $this->basePath('app'), $path);
    }

    public function publicPath(string $path = ''): string
    {
        return $this->joinPaths($this->_publicPath ?? $this->basePath('public'), $path);
    }

    public function varPath(string $path = ''): string
    {
        return $this->joinPaths($this->_varPath ?? $this->basePath('var'), $path);
    }

    public function configPath(string $path = ''): string
    {
        return $this->joinPaths($this->_configPath ?? $this->basePath('config'), $path);
    }

    public function dbPath(string $path = ''): string
    {
        return $this->joinPaths($this->_dbPath ?? $this->basePath('db'), $path);
    }

    public function cachePath(string $path = ''): string
    {
        return $this->joinPaths($this->_cachePath ?? $this->varPath('cache'), $path);
    }

    public function logPath(string $path = ''): string
    {
        return $this->joinPaths($this->_logPath ?? $this->varPath('logs'), $path);
    }

    public function uiPath(string $path = ''): string
    {
        return $this->joinPaths($this->_uiPath ?? $this->basePath('ui'), $path);
    }

    /** Setters (fluent) */
    public function useAppPath(string $path): static
    {
        $this->_appPath = $path;

        return $this;
    }

    public function usePublicPath(string $path): static
    {
        $this->_publicPath = $path;

        return $this;
    }

    public function useVarPath(string $path): static
    {
        $this->_varPath = $path;

        return $this;
    }

    public function useConfigPath(string $path): static
    {
        $this->_configPath = $path;

        return $this;
    }

    public function useDbPath(string $path): static
    {
        $this->_dbPath = $path;

        return $this;
    }

    public function useCachePath(string $path): static
    {
        $this->_cachePath = $path;

        return $this;
    }

    public function useLogPath(string $path): static
    {
        $this->_logPath = $path;

        return $this;
    }

    public function useUiPath(string $path): static
    {
        $this->_uiPath = $path;

        return $this;
    }

    /** Bind paths in Container */
    public function bindPathsInContainer(): void
    {
        $this->bindPath('path.base', $this->basePath());
        $this->bindPath('path.app', $this->appPath());
        $this->bindPath('path.public', $this->publicPath());
        $this->bindPath('path.var', $this->varPath());
        $this->bindPath('path.config', $this->configPath());
        $this->bindPath('path.db', $this->dbPath());
        $this->bindPath('path.cache', $this->cachePath());
        $this->bindPath('path.log', $this->logPath());
        $this->bindPath('path.ui', $this->uiPath());
    }

    /** Helpers */
    protected function joinPaths(string $base, string $path = ''): string
    {
        $joined = $path
            ? rtrim($base, '/\\').DIRECTORY_SEPARATOR.ltrim($path, '/\\')
            : $base;

        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $joined);
    }
}
