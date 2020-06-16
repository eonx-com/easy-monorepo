<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Laravel\Interfaces;

use EonX\EasySecurity\Interfaces\SecurityContextInterface;

interface DeferredContextResolverInterface
{
    public function resolve(): SecurityContextInterface;
}
