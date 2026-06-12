<?php

declare(strict_types=1);

namespace Horizon\Http\Response;

use Horizon\Contracts\Http\Response\ResponseContract;

class Response implements ResponseContract
{
    /**
     * @var array<string, string>
     */
    protected const DEFAULT_HEADERS = [
        'Content-Type' => 'text/html; charset=UTF-8',
    ];

    protected int $statusCode;

    protected string $body;

    /**
     * @var array<string, string>
     */
    protected array $headers;

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function __construct(string $body = '', int $statusCode = 200, array $headers = [])
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $this->normalizeHeaders(array_merge(self::DEFAULT_HEADERS, $headers));
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$this->normalizeHeaderName($name)] ?? null;
    }

    public function withStatus(int $code): static
    {
        $clone = clone $this;
        $clone->statusCode = $code;

        return $clone;
    }

    public function withBody(string $body): static
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    public function withHeader(string $name, string $value): static
    {
        $clone = clone $this;
        $clone->headers[$this->normalizeHeaderName($name)] = $value;

        return $clone;
    }

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function withHeaders(array $headers): static
    {
        $clone = clone $this;
        foreach ($headers as $name => $value) {
            $clone->headers[$this->normalizeHeaderName((string) $name)] = (string) $value;
        }

        return $clone;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        if (! headers_sent()) {
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }

        echo $this->body;
    }

    /**
     * @param  array<string, scalar|null>  $headers
     * @return array<string, string>
     */
    protected function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $value) {
            $normalized[$this->normalizeHeaderName((string) $name)] = (string) $value;
        }

        return $normalized;
    }

    protected function normalizeHeaderName(string $name): string
    {
        return implode('-', array_map(
            static fn (string $part): string => ucfirst(strtolower($part)),
            explode('-', $name)
        ));
    }
}
