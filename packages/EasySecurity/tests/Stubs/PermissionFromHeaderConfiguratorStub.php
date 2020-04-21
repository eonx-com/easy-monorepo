<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Configurators\AbstractFromHeaderConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class PermissionFromHeaderConfiguratorStub extends AbstractFromHeaderConfigurator
{
    /**
     * @var string
     */
    private $permission;

    public function __construct(string $permission, array $headerNames, ?int $priority = null)
    {
        $this->permission = $permission;

        parent::__construct($headerNames, $priority);
    }

    protected function doConfigure(SecurityContextInterface $context, Request $request, string $header): void
    {
        $context->addPermissions($this->permission);
    }
}
