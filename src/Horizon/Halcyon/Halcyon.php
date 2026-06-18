<?php

declare(strict_types=1);

namespace Horizon\Halcyon;

use Horizon\Contracts\Halcyon\OrmConfiguratorContract;

final class Halcyon implements OrmConfiguratorContract
{
    /**
     * @var array<class-string, list<class-string>>
     */
    private array $observers = [];

    /**
     * @var array<class-string, list<class-string>>
     */
    private array $scopes = [];

    /**
     * @var array<string, class-string>
     */
    private array $morphMap = [];

    public function observe(string $model, string $observer): void
    {
        $this->observers[$model][] = $observer;
    }

    public function scope(string $model, string $scope): void
    {
        $this->scopes[$model][] = $scope;
    }

    public function morphMap(array $map): void
    {
        $this->morphMap = array_merge($this->morphMap, $map);
    }

    public function getObservers(): array
    {
        return $this->observers;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function getMorphMap(): array
    {
        return $this->morphMap;
    }
}
