<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Traits;

use EonX\EasyUtils\Interfaces\HasPriorityInterface;

trait HasPriorityTrait
{
    protected int $priority = HasPriorityInterface::DEFAULT_PRIORITY;

    public function getPriority(): int
    {
        return $this->priority;
    }

    protected function doSetPriority(?int $priority = null): void
    {
        if ($priority !== null) {
            $this->priority = $priority;
        }
    }
}
