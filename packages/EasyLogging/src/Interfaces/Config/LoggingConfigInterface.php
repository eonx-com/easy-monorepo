<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces\Config;

interface LoggingConfigInterface
{
    /**
     * @return null|string[]
     */
    public function channels(): ?array;

    public function priority(): int;
}
