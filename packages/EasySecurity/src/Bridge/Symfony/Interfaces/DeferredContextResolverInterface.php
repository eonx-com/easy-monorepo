<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Interfaces;

use EonX\EasySecurity\Interfaces\ContextInterface;

interface DeferredContextResolverInterface
{
    public function resolve(): ContextInterface;
}
