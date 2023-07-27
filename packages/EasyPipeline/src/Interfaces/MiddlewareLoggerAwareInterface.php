<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Interfaces;

interface MiddlewareLoggerAwareInterface
{
    public function setLogger(MiddlewareLoggerInterface $logger): void;
}
