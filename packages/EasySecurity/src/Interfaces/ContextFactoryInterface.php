<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface ContextFactoryInterface
{
    public function create(): ContextInterface;
}
