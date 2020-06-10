<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated Since 2.4, will be removed in 3.0, use EonX\EasySecurity\Interfaces\SecurityContextConfiguratorInterface
 *     instead
 */
interface ContextModifierInterface
{
    public function getPriority(): int;

    public function modify(ContextInterface $context, Request $request): void;
}
