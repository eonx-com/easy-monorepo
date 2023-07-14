<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasyApiToken\Interfaces\Tokens\ApiKeyInterface;
use EonX\EasySecurity\Configurators\AbstractFromApiKeyConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class PermissionFromApiKeyConfiguratorStub extends AbstractFromApiKeyConfigurator
{
    public function __construct(
        private string $permission,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    protected function doConfigure(
        SecurityContextInterface $context,
        Request $request,
        ApiKeyInterface $apiKey,
    ): void {
        $context->addPermissions($this->permission);
    }
}
