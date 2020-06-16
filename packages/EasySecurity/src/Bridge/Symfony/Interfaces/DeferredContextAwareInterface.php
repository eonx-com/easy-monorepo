<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Interfaces;

/**
 * @deprecated Since 2.4, will be removed in 3.0. Use DeferredSecurityContextAwareInterface instead.
 */
interface DeferredContextAwareInterface
{
    public function setDeferredContextResolver(DeferredContextResolverInterface $contextResolver): void;
}
