<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Configurators;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\RolesProviderInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class RolesFromJwtConfigurator extends AbstractFromJwtConfigurator
{
    /**
     * @var \EonX\EasySecurity\Interfaces\RolesProviderInterface
     */
    private $rolesProvider;

    public function __construct(string $jwtClaim, RolesProviderInterface $rolesProvider, ?int $priority = null)
    {
        $this->rolesProvider = $rolesProvider;

        parent::__construct($jwtClaim, $priority);
    }

    protected function doConfigure(
        SecurityContextInterface $context,
        Request $request,
        JwtEasyApiTokenInterface $token
    ): void {
        $roles = $this->rolesProvider->getRolesByIdentifiers($this->getMainClaim($token)['roles'] ?? []);

        if (empty($roles)) {
            return;
        }

        $context->addRoles($roles);
    }
}
