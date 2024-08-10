<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization\Configurator;

use EonX\EasyApiToken\Common\ValueObject\JwtToken;
use EonX\EasySecurity\Common\Configurator\AbstractFromJwtConfigurator;
use EonX\EasySecurity\Common\Context\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

final class RolesFromJwtConfigurator extends AbstractFromJwtConfigurator
{
    protected function doConfigure(SecurityContextInterface $context, Request $request, JwtToken $jwtToken): void
    {
        $roles = $context->getAuthorizationMatrix()
            ->getRolesByIdentifiers($this->getMainClaim($jwtToken)['roles'] ?? []);

        if (\count($roles) === 0) {
            return;
        }

        $context->addRoles($roles);
    }
}
