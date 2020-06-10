<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Interfaces;

interface DeferredSecurityContextAwareInterface extends DeferredContextAwareInterface
{
    public function setDeferredSecurityContextResolver(DeferredSecurityContextResolverInterface $contextResolver): void;
}
