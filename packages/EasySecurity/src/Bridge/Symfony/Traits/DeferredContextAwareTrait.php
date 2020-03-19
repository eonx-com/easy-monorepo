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

    public function setDeferredContextResolver(DeferredContextResolverInterface $contextResolver): void
    {
        $this->contextResolver = $contextResolver;
    }

    protected function resolveContext(): ContextInterface
    {
        return $this->contextResolver->resolve();
    }
}
