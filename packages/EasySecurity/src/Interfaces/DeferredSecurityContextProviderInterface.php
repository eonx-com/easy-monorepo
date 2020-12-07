<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface DeferredSecurityContextProviderInterface
{
    public function getSecurityContext(): SecurityContextInterface;
}
