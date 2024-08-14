<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Configurator;

use EonX\EasyApiToken\Common\ValueObject\ApiKey;
use EonX\EasySecurity\Common\Configurator\AbstractFromApiKeyConfigurator;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class PermissionFromApiKeyConfiguratorStub extends AbstractFromApiKeyConfigurator
{
    public function __construct(
        private readonly string $permission,
        ?int $priority = null,
    ) {
        parent::__construct($priority);
    }

    protected function doConfigure(
        SecurityContextInterface $context,
        Request $request,
        ApiKey $apiKey,
    ): void {
        $context->addPermissions($this->permission);
    }
}
