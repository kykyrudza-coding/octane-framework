<?php

declare(strict_types=1);

namespace Horizon\Prism;

use Horizon\Contracts\Prism\ViewContract;
use Stringable;

/**
 * Immutable wrapper for an already-rendered view string.
 * Returned by ViewFactory::make() after engine evaluation.
 */
final readonly class RenderedView implements ViewContract, Stringable
{
    public function __construct(
        private string $content,
    ) {}

    public function with(string|array $key, mixed $value = null): static
    {
        // RenderedView is already evaluated; with() is a no-op.
        return $this;
    }

    public function render(): string
    {
        return $this->content;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
