<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Factory;

interface LazyLoggerFactoryInterface extends LoggerFactoryInterface
{
    public function initLazyLogger(string $channel): self;
}
