<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Helper;

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
