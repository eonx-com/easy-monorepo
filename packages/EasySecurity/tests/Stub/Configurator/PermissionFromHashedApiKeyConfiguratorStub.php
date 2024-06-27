<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Configurator;

use EonX\EasyApiToken\Common\ValueObject\HashedApiKeyInterface;
use EonX\EasySecurity\Common\Configurator\AbstractFromHashedApiKeyConfigurator;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;
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
