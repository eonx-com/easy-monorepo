<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Interfaces;

interface MiddlewareLoggerAwareInterface
{
    /**
     * Set middleware logger.
     *
     * @param \StepTheFkUp\EasyPipeline\Interfaces\MiddlewareLoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(MiddlewareLoggerInterface $logger): void;
}
