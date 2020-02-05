<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Traits;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\DeferredContextResolverInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;

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

    /**
     * Resolve context.
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    protected function resolveContext(): ContextInterface
    {
        return $this->contextResolver->resolve();
    }
}
