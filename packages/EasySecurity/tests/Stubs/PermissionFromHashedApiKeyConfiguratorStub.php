<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasyApiToken\Interfaces\Tokens\HashedApiKeyInterface;
use EonX\EasySecurity\Configurators\AbstractFromHashedApiKeyConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class PermissionFromHashedApiKeyConfiguratorStub extends AbstractFromHashedApiKeyConfigurator
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
        HashedApiKeyInterface $apiKey,
    ): void {
        $context->addPermissions($this->permission);
    }
}
