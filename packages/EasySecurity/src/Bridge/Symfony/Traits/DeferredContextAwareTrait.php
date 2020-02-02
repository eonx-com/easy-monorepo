<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Traits;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredContextResolverInterface;

trait DeferredContextAwareTrait
{
    /**
     * @var \EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredContextResolverInterface
     */
    protected $contextResolver;

    /**
     * Set deferred context resolver.
     *
     * @param \EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredContextResolverInterface $contextResolver
     *
     * @return void
     */
    public function setDeferredContextResolver(DeferredContextResolverInterface $contextResolver): void
    {
        $this->contextResolver = $contextResolver;
    }
}
