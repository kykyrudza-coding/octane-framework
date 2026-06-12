<?php

declare(strict_types=1);

namespace Horizon\Http\Response;

use JsonException;
use RuntimeException;

class JsonResponse extends Response
{
    protected mixed $data;

    /**
     * @param  array<string, scalar|null>  $headers
     */
    public function __construct(mixed $data = null, int $statusCode = 200, array $headers = [])
    {
        $this->data = $data;

        $headers = array_merge($headers, [
            'Content-Type' => 'application/json',
        ]);

        parent::__construct('', $statusCode, $headers);
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): static
    {
        $clone = clone $this;
        $clone->data = $data;

        return $clone;
    }

    public function send(): void
    {
        try {

            $this->body = json_encode($this->data, JSON_THROW_ON_ERROR);

        } catch (JsonException $e) {
            throw new RuntimeException('Failed to encode JSON response: '.$e->getMessage(), 0, $e);
        }

        parent::send();
    }
}
