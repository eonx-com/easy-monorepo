<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface;
use Monolog\Handler\HandlerInterface;

final class HandlerConfig extends AbstractLoggingConfig implements HandlerConfigInterface
{
    /**
     * @var \Monolog\Handler\HandlerInterface
     */
    private $handler;

    /**
     * @param null|string[] $handler
     */
    public function __construct(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public static function create(HandlerInterface $handler): self
    {
        return new self($handler);
    }

    public function handler(): HandlerInterface
    {
        return $this->handler;
    }
}
