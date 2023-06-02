<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasyApiToken\Interfaces\Tokens\ApiKeyInterface;
use EonX\EasySecurity\Configurators\AbstractFromApiKeyConfigurator;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class PermissionFromApiKeyConfiguratorStub extends AbstractFromApiKeyConfigurator
{
    /**
     * @var string
     */
    private $permission;

    public function __construct(string $permission, ?int $priority = null)
    {
        $this->permission = $permission;

        parent::__construct($priority);
    }

    protected function doConfigure(
        SecurityContextInterface $context,
        Request $request,
        ApiKeyInterface $apiKey
    ): void {
        $context->addPermissions($this->permission);
    }
}
