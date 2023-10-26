<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces;

interface LazyLoggerFactoryInterface extends LoggerFactoryInterface
{
    public function initLazyLogger(string $channel): self;
}
