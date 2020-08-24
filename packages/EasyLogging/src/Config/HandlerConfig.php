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
     * @param null|string[] $channels
     */
    public function __construct(HandlerInterface $handler, ?array $channels = null, ?int $priority = null)
    {
        $this->handler = $handler;

        parent::__construct($channels, $priority);
    }

    public function handler(): HandlerInterface
    {
        return $this->handler;
    }
}
