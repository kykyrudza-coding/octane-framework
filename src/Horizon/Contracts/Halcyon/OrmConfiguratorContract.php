<?php

declare(strict_types=1);

namespace Horizon\Contracts\Halcyon;

interface OrmConfiguratorContract
{
    /**
     * @param  class-string  $model
     * @param  class-string  $observer
     */
    public function observe(string $model, string $observer): void;

    /**
     * @param  class-string  $model
     * @param  class-string  $scope
     */
    public function scope(string $model, string $scope): void;

    /**
     * @param  array<string, class-string>  $map
     */
    public function morphMap(array $map): void;

    /**
     * @return array<class-string, list<class-string>>
     */
    public function getObservers(): array;

    /**
     * @return array<class-string, list<class-string>>
     */
    public function getScopes(): array;

    /**
     * @return array<string, class-string>
     */
    public function getMorphMap(): array;
}
