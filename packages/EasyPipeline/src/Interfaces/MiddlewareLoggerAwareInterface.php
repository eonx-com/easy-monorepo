<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPipeline\Interfaces;

interface MiddlewareLoggerAwareInterface
{
    /**
     * Set middleware logger.
     *
     * @param \LoyaltyCorp\EasyPipeline\Interfaces\MiddlewareLoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(MiddlewareLoggerInterface $logger): void;
}


