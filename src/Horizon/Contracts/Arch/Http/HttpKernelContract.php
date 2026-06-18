<?php

declare(strict_types=1);

namespace Horizon\Contracts\Arch\Http;

use Horizon\Contracts\Arch\ApplicationContract;
use Horizon\Contracts\Http\Request\RequestContextContract;
use Horizon\Contracts\Http\Response\ResponseContract;

interface HttpKernelContract
{
    public function handle(RequestContextContract $requestContext): ResponseContract;

    public function terminate(RequestContextContract $requestContext, ResponseContract $response): void;

    public function getApplication(): ApplicationContract;
}
