<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Factory;

use EonX\EasySecurity\Common\Context\SecurityContext;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;

final class SecurityContextFactory implements SecurityContextFactoryInterface
{
    public function create(): SecurityContextInterface
    {
        return new SecurityContext();
    }
}
