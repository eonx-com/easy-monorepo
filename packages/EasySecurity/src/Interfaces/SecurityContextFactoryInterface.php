<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface SecurityContextFactoryInterface
{
    public function create(): SecurityContextInterface;
}
