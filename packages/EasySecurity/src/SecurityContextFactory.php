<?php
declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\SecurityContextFactoryInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;

final class SecurityContextFactory implements SecurityContextFactoryInterface
{
    public function create(): SecurityContextInterface
    {
        return new SecurityContext();
    }
}
