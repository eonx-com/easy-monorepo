<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Logger;

interface MiddlewareLoggerInterface
{
    public function log(string $middleware, mixed $content): void;
}
