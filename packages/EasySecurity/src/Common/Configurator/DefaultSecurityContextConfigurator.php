<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Configurator;

use EonX\EasySecurity\Common\Context\SecurityContextInterface;

final class DefaultSecurityContextConfigurator
{
    public function __invoke(SecurityContextInterface $securityContext): SecurityContextInterface
    {
        return $securityContext;
    }
}
