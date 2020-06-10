<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Traits;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredSecurityContextResolverInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;

trait DeferredSecurityContextAwareTrait
{
    /**
     * @var \EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredSecurityContextResolverInterface
     */
    private $securityContextResolver;

    public function setDeferredSecurityContextResolver(DeferredSecurityContextResolverInterface $resolver): void
    {
        $this->securityContextResolver = $resolver;
    }

    protected function resolveSecurityContext(): SecurityContextInterface
    {
        return $this->securityContextResolver->resolve();
    }
}
