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

    public function reset(): void
    {
        @\trigger_error(
            'reset() is deprecated since 3.3 and will be removed in 4.0. Factory isn\'t resettable anymore',
            \E_USER_DEPRECATED
        );
    }
}
