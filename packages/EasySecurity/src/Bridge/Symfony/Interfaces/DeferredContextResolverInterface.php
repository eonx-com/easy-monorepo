<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Interfaces;

use EonX\EasySecurity\Interfaces\SecurityContextInterface;

/**
 * @deprecated Since 2.4, will be removed in 3.0. Use DeferredSecurityContextResolverInterface instead.
 */
interface DeferredContextResolverInterface
{
    public function resolve(): SecurityContextInterface;
}
