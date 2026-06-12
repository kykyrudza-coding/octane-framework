<?php

declare(strict_types=1);

namespace Horizon\Prism;

use Horizon\Contracts\Prism\ViewContract;
use Horizon\Contracts\Prism\ViewFactoryContract;
use Stringable;

final class View implements ViewContract, Stringable
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function __construct(
        private readonly ViewFactoryContract $factory,
        private readonly string $view,
        array $data = [],
    ) {
        $this->data = $data;
    }

    /**
     * Add data to the view.
     *
     * @param  string|array<string, mixed>  $key
     */
    public function with(string|array $key, mixed $value = null): static
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    public function render(): string
    {
        return $this->factory->make($this->view, $this->data)->render();
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
