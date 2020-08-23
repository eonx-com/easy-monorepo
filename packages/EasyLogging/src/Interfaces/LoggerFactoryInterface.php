<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces;

use Monolog\Logger;

interface LoggerFactoryInterface
{
    /**
     * @var string
     */
    public const DEFAULT_CHANNEL = 'app';

    public function create(?string $channel = null): Logger;
}
