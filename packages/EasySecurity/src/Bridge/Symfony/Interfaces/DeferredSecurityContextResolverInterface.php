<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Interfaces;

use EonX\EasySecurity\Interfaces\SecurityContextInterface;

interface DeferredSecurityContextResolverInterface extends DeferredContextResolverInterface
{
    public function resolve(): SecurityContextInterface;
}
