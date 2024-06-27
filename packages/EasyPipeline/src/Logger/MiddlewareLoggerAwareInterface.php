<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Logger;

interface MiddlewareLoggerAwareInterface
{
    public function setLogger(MiddlewareLoggerInterface $logger): void;
}
