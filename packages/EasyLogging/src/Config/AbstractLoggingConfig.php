<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Config;

use EonX\EasyLogging\Interfaces\Config\LoggingConfigInterface;

abstract class AbstractLoggingConfig implements LoggingConfigInterface
{
    /**
     * @var string[]|null
     */
    private ?array $channels = null;

    /**
     * @var string[]|null
     */
    private ?array $exceptChannels = null;

    private ?int $priority = null;

    /**
     * @param string[]|null $channels
     */
    public function channels(?array $channels = null): LoggingConfigInterface
    {
        $this->channels = $channels;

        return $this;
    }

    /**
     * @param string[]|null $exceptChannels
     */
    public function exceptChannels(?array $exceptChannels = null): LoggingConfigInterface
    {
        $this->exceptChannels = $exceptChannels;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getChannels(): ?array
    {
        return $this->channels;
    }

    /**
     * @return string[]|null
     */
    public function getExceptChannels(): ?array
    {
        return $this->exceptChannels;
    }

    public function getPriority(): int
    {
        return $this->priority ?? 0;
    }

    public function priority(?int $priority = null): LoggingConfigInterface
    {
        $this->priority = $priority;

        return $this;
    }
}
