<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Traits;

use LoyaltyCorp\EasyPipeline\Interfaces\MiddlewareLoggerInterface;

trait MiddlewareLoggerAwareTrait
{
    /**
     * @var \LoyaltyCorp\EasyPipeline\Interfaces\MiddlewareLoggerInterface
     */
    private $logger;

    /**
     * Set middleware logger.
     *
     * @param \LoyaltyCorp\EasyPipeline\Interfaces\MiddlewareLoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(MiddlewareLoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Log given content.
     *
     * @param mixed $content Content to log
     * @param null|string $middleware Default to current class
     *
     * @return void
     */
    private function log($content, ?string $middleware = null): void
    {
        $this->logger->log($middleware ?? \get_class($this), $content);
    }
}

\class_alias(
    MiddlewareLoggerAwareTrait::class,
    'StepTheFkUp\EasyPipeline\Traits\MiddlewareLoggerAwareTrait',
    false
);
