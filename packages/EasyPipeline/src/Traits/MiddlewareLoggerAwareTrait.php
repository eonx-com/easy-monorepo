<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Traits;

use EonX\EasyPipeline\Interfaces\MiddlewareLoggerInterface;

trait MiddlewareLoggerAwareTrait
{
    /**
     * @var \EonX\EasyPipeline\Interfaces\MiddlewareLoggerInterface
     */
    private $logger;

    public function setLogger(MiddlewareLoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param mixed $content Content to log
     */
    private function log($content, ?string $middleware = null): void
    {
        $this->logger->log($middleware ?? static::class, $content);
    }
}
