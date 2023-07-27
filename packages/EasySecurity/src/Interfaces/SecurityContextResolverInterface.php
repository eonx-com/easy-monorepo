<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface SecurityContextResolverInterface
{
    public function resolveContext(): SecurityContextInterface;

    public function setConfigurator(callable $configurator): self;
}
