<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Modifiers;

use EonX\EasySecurity\Interfaces\ContextModifierInterface;
use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface;

/**
 * @deprecated Since 2.4, will be removed in 3.0.
 */
abstract class AbstractContextModifier implements ContextModifierInterface
{
    /**
     * @var null|int
     */
    private $priority;

    public function __construct(?int $priority = null)
    {
        $this->priority = $priority;

        @\trigger_error(
            \sprintf(
                'Using %s is deprecated since 2.4 and will be removed in 3.0. Use %s instead',
                ContextModifierInterface::class,
                SecurityContextConfiguratorInterface::class
            ),
            \E_USER_DEPRECATED
        );
    }

    public function getPriority(): int
    {
        return $this->priority ?? 0;
    }
}
