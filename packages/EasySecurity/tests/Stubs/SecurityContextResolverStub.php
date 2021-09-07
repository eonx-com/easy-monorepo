<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use EonX\EasySecurity\Interfaces\SecurityContextResolverInterface;

final class SecurityContextResolverStub implements SecurityContextResolverInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\SecurityContextInterface
     */
    private $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function resolveContext(): SecurityContextInterface
    {
        return $this->securityContext;
    }

    public function setConfigurator(callable $configurator): SecurityContextResolverInterface
    {
        return $this;
    }
}
