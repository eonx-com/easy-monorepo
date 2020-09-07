<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces\Config;

interface LoggingConfigInterface
{
    /**
     * @param null|string[] $channels
     */
    public function channels(?array $channels = null): self;

    /**
     * @param null|string[] $exceptChannels
     */
    public function exceptChannels(?array $exceptChannels = null): self;

    /**
     * @return null|string[]
     */
    public function getChannels(): ?array;

    /**
     * @return null|string[]
     */
    public function getExceptChannels(): ?array;

    public function getPriority(): int;

    public function priority(?int $priority = null): self;
}
