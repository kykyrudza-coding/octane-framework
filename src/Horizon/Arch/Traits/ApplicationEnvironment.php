<?php

declare(strict_types=1);

namespace Horizon\Arch\Traits;

use Horizon\Support\Enums\Environment;

trait ApplicationEnvironment
{
    protected string $environment = 'production';

    protected string $environmentFile = '.env';

    protected string $developmentEnvironmentFile = '.env.development';

    protected string $productionEnvironmentFile = '.env.production';

    protected string $testingEnvironmentFile = '.env.testing';

    protected string $localEnvironmentFile = '.env.local';

    /** Set environment and get environment */
    public function setEnvironment(string $environment = 'production'): void
    {
        $this->environment = $environment;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /** Check if the environment is */
    public function isProduction(): bool
    {
        return $this->environment === Environment::PRODUCTION->value;
    }

    public function isDevelop(): bool
    {
        return $this->environment === Environment::DEVELOPMENT->value;
    }

    public function isTesting(): bool
    {
        return $this->environment === Environment::TESTING->value;
    }

    public function isLocal(): bool
    {
        return $this->environment === Environment::LOCAL->value;
    }

    /** Normalize path separators */
    protected function normalizeEnvPath(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    /** Fluent setters */

    /** Set an environment file */
    public function environmentFile(string $environmentFile): static
    {
        $this->environmentFile = $this->normalizeEnvPath($environmentFile);

        return $this;
    }

    /** Set a development environment file */
    public function developmentEnvironmentFile(string $developmentEnvironmentFile): static
    {
        $this->developmentEnvironmentFile = $this->normalizeEnvPath($developmentEnvironmentFile);

        return $this;
    }

    /** Set a production environment file */
    public function productionEnvironmentFile(string $productionEnvironmentFile): static
    {
        $this->productionEnvironmentFile = $this->normalizeEnvPath($productionEnvironmentFile);

        return $this;
    }

    /** Set a testing environment file */
    public function testingEnvironmentFile(string $testingEnvironmentFile): static
    {
        $this->testingEnvironmentFile = $this->normalizeEnvPath($testingEnvironmentFile);

        return $this;
    }

    /** Set a local environment file */
    public function localEnvironmentFile(string $localEnvironmentFile): static
    {
        $this->localEnvironmentFile = $this->normalizeEnvPath($localEnvironmentFile);

        return $this;
    }

    /** Get base environment file */
    public function getEnvironmentFile(): string
    {
        return $this->environmentFile;
    }

    /** Get a development environment file */
    public function getDevelopmentEnvironmentFile(): string
    {
        return $this->developmentEnvironmentFile;
    }

    /** Get a production environment file */
    public function getProductionEnvironmentFile(): string
    {
        return $this->productionEnvironmentFile;
    }

    /** Get a testing environment file */
    public function getTestingEnvironmentFile(): string
    {
        return $this->testingEnvironmentFile;
    }

    /** Get a local environment file */
    public function getLocalEnvironmentFile(): string
    {
        return $this->localEnvironmentFile;
    }
}
