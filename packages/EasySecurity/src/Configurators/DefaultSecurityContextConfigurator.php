<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Configurators;

use EonX\EasySecurity\Interfaces\SecurityContextInterface;

final class DefaultSecurityContextConfigurator
{
    public function __invoke(SecurityContextInterface $securityContext): SecurityContextInterface
    {
        return $securityContext;
    }
}
