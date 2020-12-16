<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\SecurityContextInterface;

final class SecurityContextFactory extends AbstractSecurityContextFactory
{
    protected function doCreate(): SecurityContextInterface
    {
        return new SecurityContext();
    }
}
