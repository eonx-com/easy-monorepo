<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Configurators;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class RolesFromJwtConfigurator extends AbstractFromJwtConfigurator
{
    protected function doConfigure(
        SecurityContextInterface $context,
        Request $request,
        JwtEasyApiTokenInterface $token
    ): void {
        $roles = $context->getAuthorizationMatrix()->getRolesByIdentifiers($this->getMainClaim($token)['roles'] ?? []);

        if (empty($roles)) {
            return;
        }

        $context->addRoles($roles);
    }
}
