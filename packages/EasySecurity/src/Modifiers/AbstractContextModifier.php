<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Modifiers;

use EonX\EasySecurity\Interfaces\ContextModifierInterface;

abstract class AbstractContextModifier implements ContextModifierInterface
{
    /**
     * @var null|int
     */
    private $priority;

    public function __construct(?int $priority = null)
    {
        $this->priority = $priority;
    }

    public function getPriority(): int
    {
        return $this->priority ?? 0;
    }
}
