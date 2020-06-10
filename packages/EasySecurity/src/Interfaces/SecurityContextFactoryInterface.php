<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface SecurityContextFactoryInterface extends ContextFactoryInterface
{
    public function create(): SecurityContextInterface;
}
