<?php

declare(strict_types=1);

namespace Horizon\Http\Response;

class RedirectResponse extends Response
{
    protected string $targetUrl;

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function __construct(string $url, int $statusCode = 302, array $headers = [])
    {
        $this->targetUrl = $url;

        $headers = array_merge($headers, [
            'Location' => $url,
        ]);

        parent::__construct('', $statusCode, $headers);
    }

    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }

    public function setTargetUrl(string $url): static
    {
        $clone = clone $this;
        $clone->targetUrl = $url;

        $clone->headers[$this->normalizeHeaderName('Location')] = $url;

        return $clone;
    }

    // коли будуть сесії
    // public function with(string $key, mixed $value): static
    // {
    //     // app()->make(Session::class)->flash($key, $value);
    //     return $this;
    // }
}
