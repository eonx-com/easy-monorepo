<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces;

use Psr\Log\LoggerInterface;

interface LoggerFactoryInterface
{
    public function create(?string $channel = null): LoggerInterface;
}
