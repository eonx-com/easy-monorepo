<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\SecurityContextInterface;

/**
 * Not final on purpose for BC compatibility until 3.0.
 */
final class SecurityContextFactory extends AbstractSecurityContextFactory
{
    protected function doCreate(): SecurityContextInterface
    {
        return new SecurityContext();
    }
}
