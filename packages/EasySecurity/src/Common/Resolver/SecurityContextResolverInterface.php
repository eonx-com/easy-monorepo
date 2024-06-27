<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Resolver;

use EonX\EasySecurity\Common\Context\SecurityContextInterface;

interface SecurityContextResolverInterface
{
    public function resolveContext(): SecurityContextInterface;

    public function setConfigurator(callable $configurator): self;
}
