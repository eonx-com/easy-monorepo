<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Configurator;

use EonX\EasySecurity\Common\Configurator\AbstractSecurityContextConfigurator;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class StopPropagationConfiguratorStub extends AbstractSecurityContextConfigurator
{
    public function __construct(?int $priority = null)
    {
        parent::__construct($priority);
    }

    public function configure(SecurityContextInterface $context, Request $request): void
    {
        $context->addPermissions('stop');
        $this->stopPropagation();
    }
}
