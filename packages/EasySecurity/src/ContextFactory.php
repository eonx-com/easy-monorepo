<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\ContextFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;

/**
 * @deprecated Since 2.4, will be removed in 3.0. Use SecurityContextFactory instead.
 */
final class ContextFactory implements ContextFactoryInterface
{
    public function create(): ContextInterface
    {
        return new SecurityContext();
    }
}
