<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Interfaces;

interface DeferredContextAwareInterface
{
    /**
     * Set deferred context resolver.
     *
     * @param \EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredContextResolverInterface $contextResolver
     *
     * @return void
     */
    public function setDeferredContextResolver(DeferredContextResolverInterface $contextResolver): void;
}
