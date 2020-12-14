<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Interfaces;

interface HasPriorityInterface
{
    /**
     * @var int
     */
    public const DEFAULT_PRIORITY = 0;

    public function getPriority(): int;
}
