<?php

declare(strict_types=1);

namespace Horizon\Contracts\Http\Response;

interface ResponseContract
{
    public function getStatusCode(): int;

    public function getBody(): string;

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array;

    public function getHeader(string $name): ?string;

    public function withStatus(int $code): static;

    public function withBody(string $body): static;

    public function withHeader(string $name, string $value): static;

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function withHeaders(array $headers): static;

    public function send(): void;
}
