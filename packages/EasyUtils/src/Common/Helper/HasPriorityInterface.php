<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Helper;

interface HasPriorityInterface
{
    public const DEFAULT_PRIORITY = 0;

    public function getPriority(): int;
}
