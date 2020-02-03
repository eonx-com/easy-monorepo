<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Interfaces;

use EonX\EasySecurity\Interfaces\ContextInterface;

interface DeferredContextResolverInterface
{
    /**
     * Resolve context.
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    public function resolve(): ContextInterface;
}
