<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Interfaces;

use EonX\EasySecurity\Interfaces\ContextInterface;

interface DeferredContextResolverInterface
{
    public function resolve(): ContextInterface;
}
