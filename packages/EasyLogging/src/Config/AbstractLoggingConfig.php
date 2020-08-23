<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use EonX\EasyLogging\Interfaces\Config\LoggingConfigInterface;

abstract class AbstractLoggingConfig implements LoggingConfigInterface
{
    /**
     * @var null|string[]
     */
    private $channels;

    /**
     * @var int
     */
    private $priority;

    /**
     * @param null|string[] $channels
     */
    public function __construct(?array $channels = null, ?int $priority = null)
    {
        $this->channels = $channels;
        $this->priority = $priority ?? 0;
    }

    /**
     * @return null|string[]
     */
    public function channels(): ?array
    {
        return $this->channels;
    }

    public function priority(): int
    {
        return $this->priority;
    }
}
