<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Configurators\AbstractFromHeaderConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class PermissionFromHeaderConfiguratorStub extends AbstractFromHeaderConfigurator
{
    public function __construct(
        private string $permission,
        array $headerNames,
        ?int $priority = null,
    ) {
        parent::__construct($headerNames, $priority);
    }

    protected function doConfigure(SecurityContextInterface $context, Request $request, string $header): void
    {
        $context->addPermissions($this->permission);
    }
}
