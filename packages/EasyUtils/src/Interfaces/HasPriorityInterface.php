<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Interfaces;

interface HasPriorityInterface
{
    public const DEFAULT_PRIORITY = 0;

    public function getPriority(): int;
}
