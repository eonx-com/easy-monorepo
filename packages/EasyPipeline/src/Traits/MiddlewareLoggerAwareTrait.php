<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Traits;

use EonX\EasyPipeline\Interfaces\MiddlewareLoggerInterface;

trait MiddlewareLoggerAwareTrait
{
    private MiddlewareLoggerInterface $logger;

    public function setLogger(MiddlewareLoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function log(mixed $content, ?string $middleware = null): void
    {
        $this->logger->log($middleware ?? static::class, $content);
    }
}
