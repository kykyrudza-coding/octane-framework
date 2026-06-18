<?php

declare(strict_types=1);

namespace Horizon\Validation\Providers;

use Horizon\Contracts\Arch\Config\ConfigRepositoryContract;
use Horizon\Contracts\DTO\DtoFactoryContract;
use Horizon\Contracts\Validation\PresenceVerifierContract;
use Horizon\Contracts\Validation\ValidatorFactoryContract;
use Horizon\Support\Providers\ServiceProvider;
use Horizon\Validation\Presence\ArrayPresenceVerifier;
use Horizon\Validation\Presence\NullPresenceVerifier;
use Horizon\Validation\ValidatorFactory;

final class ValidationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            PresenceVerifierContract::class,
            fn () => $this->presenceVerifier(),
        );

        $this->app->singleton(
            ValidatorFactoryContract::class,
            fn () => new ValidatorFactory(
                presenceVerifier: $this->app->make(PresenceVerifierContract::class),
                dtoFactory: $this->app->has(DtoFactoryContract::class)
                    ? $this->app->make(DtoFactoryContract::class)
                    : null,
                stopOnFirstFailure: $this->configBool('validation.stop_on_first_failure', false),
                messages: $this->configArray('validation.messages'),
                attributes: $this->configArray('validation.attributes'),
            ),
        );
    }

    private function presenceVerifier(): PresenceVerifierContract
    {
        $driver = $this->configString('validation.presence.driver', 'null');

        if ($driver === 'array') {
            return new ArrayPresenceVerifier(
                $this->tableMap($this->configArray('validation.presence.tables')),
            );
        }

        $verifier = $this->configString('validation.presence.verifier', '');

        if ($verifier !== '' && class_exists($verifier)) {
            $instance = new $verifier;

            if ($instance instanceof PresenceVerifierContract) {
                return $instance;
            }
        }

        return new NullPresenceVerifier;
    }

    private function configBool(string $key, bool $default): bool
    {
        $value = $this->config($key, $default);

        return is_bool($value) ? $value : $default;
    }

    private function configString(string $key, string $default): string
    {
        $value = $this->config($key, $default);

        return is_string($value) ? $value : $default;
    }

    /**
     * @return array<string, mixed>
     */
    private function configArray(string $key): array
    {
        $value = $this->config($key, []);

        return is_array($value) ? $value : [];
    }

    private function config(string $key, mixed $default = null): mixed
    {
        if (! $this->app->has(ConfigRepositoryContract::class)) {
            return $default;
        }

        $config = $this->app->make(ConfigRepositoryContract::class);

        if (! $config instanceof ConfigRepositoryContract) {
            return $default;
        }

        return $config->get($key, $default);
    }

    /**
     * @param  array<string, mixed>  $tables
     * @return array<string, list<array<string, mixed>>>
     */
    private function tableMap(array $tables): array
    {
        $map = [];

        foreach ($tables as $table => $rows) {
            if (! is_string($table) || ! is_array($rows)) {
                continue;
            }

            $map[$table] = array_values(array_filter(
                $rows,
                static fn (mixed $row): bool => is_array($row),
            ));
        }

        return $map;
    }
}
