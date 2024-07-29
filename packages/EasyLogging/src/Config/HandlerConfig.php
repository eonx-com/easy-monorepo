<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use Monolog\Handler\HandlerInterface;

final class HandlerConfig extends AbstractLoggingConfig implements HandlerConfigInterface
{
    public function __construct(
        private readonly HandlerInterface $handler,
    ) {
    }

    public static function create(HandlerInterface $handler): HandlerConfigInterface
    {
        return new self($handler);
    }

    public function handler(): HandlerInterface
    {
        return $this->handler;
    }
}
