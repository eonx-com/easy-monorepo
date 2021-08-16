<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use SecurityContextResolverInterface instead.
 */
interface DeferredSecurityContextProviderInterface
{
    public function getSecurityContext(): SecurityContextInterface;
}
