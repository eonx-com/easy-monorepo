<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\DeferredSecurityContextProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use SecurityContextResolverInterface instead.
 */
final class DeferredSecurityContextProvider implements DeferredSecurityContextProviderInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextResolverInterface
     */
    private $securityContextResolver;

    public function __construct(SecurityContextResolverInterface $securityContextResolver)
    {
        $this->securityContextResolver = $securityContextResolver;
    }

    public function getSecurityContext(): SecurityContextInterface
    {
        @\trigger_error(\sprintf(
            '%s::getSecurityContext() is deprecated since 3.3 and will be removed in 4.0. ' .
            'Use %s::resolveContext() instead.',
            DeferredSecurityContextProviderInterface::class,
            SecurityContextResolverInterface::class
        ), \E_USER_DEPRECATED);

        return $this->securityContextResolver->resolveContext();
    }
}
