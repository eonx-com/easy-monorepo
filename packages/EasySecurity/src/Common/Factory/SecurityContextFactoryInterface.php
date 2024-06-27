<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Factory;

use EonX\EasySecurity\Common\Context\SecurityContextInterface;

interface SecurityContextFactoryInterface
{
    public function create(): SecurityContextInterface;
}
