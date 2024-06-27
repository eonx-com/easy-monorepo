<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Resolver;

use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use EonX\EasySecurity\Common\Resolver\SecurityContextResolverInterface;

final class SecurityContextResolverStub implements SecurityContextResolverInterface
{
    public function __construct(
        private SecurityContextInterface $securityContext,
    ) {
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
