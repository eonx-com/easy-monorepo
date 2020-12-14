<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Traits;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

trait HasPriorityTrait
{
    /**
     * @var int
     */
    protected $priority = HasPriorityInterface::DEFAULT_PRIORITY;

    public function getPriority(): int
    {
        return $this->priority;
    }
}
