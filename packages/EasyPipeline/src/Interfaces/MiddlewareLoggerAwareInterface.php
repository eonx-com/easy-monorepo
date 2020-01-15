<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Interfaces;

interface MiddlewareLoggerAwareInterface
{
    /**
     * Set middleware logger.
     *
     * @param \EonX\EasyPipeline\Interfaces\MiddlewareLoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(MiddlewareLoggerInterface $logger): void;
}
