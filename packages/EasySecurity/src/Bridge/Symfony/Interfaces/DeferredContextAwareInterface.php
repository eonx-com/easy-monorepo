<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Interfaces;

interface DeferredContextAwareInterface
{
    public function setDeferredContextResolver(DeferredContextResolverInterface $contextResolver): void;
}
