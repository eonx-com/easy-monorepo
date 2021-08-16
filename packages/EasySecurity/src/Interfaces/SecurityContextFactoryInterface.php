<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface SecurityContextFactoryInterface
{
    public function create(): SecurityContextInterface;

    /**
     * @deprecated since 3.3, will be removed in 4.0. Factory isn't resettable anymore.
     */
    public function reset(): void;
}
