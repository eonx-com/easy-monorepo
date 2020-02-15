<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Modifiers;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\RolesProviderInterface;
use Symfony\Component\HttpFoundation\Request;

final class RolesFromJwtModifier extends AbstractContextModifier
{
    /**
     * @var \EonX\EasySecurity\Interfaces\RolesProviderInterface
     */
    private $rolesProvider;

    /**
     * RolesFromApiTokenDataResolver constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\RolesProviderInterface $rolesProvider
     * @param null|int $priority
     */
    public function __construct(RolesProviderInterface $rolesProvider, ?int $priority = null)
    {
        $this->rolesProvider = $rolesProvider;

        parent::__construct($priority);
    }

    /**
     * Modify given context for given request.
     *
     * @param \EonX\EasySecurity\Interfaces\ContextInterface $context
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function modify(ContextInterface $context, Request $request): void
    {
        $token = $context->getToken();

        // Work only for JWT
        if ($token instanceof JwtEasyApiTokenInterface === false) {
            return;
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */

        $user = $this->getClaimSafely($token, ContextInterface::JWT_MANAGE_CLAIM, []);
        $roles = $this->rolesProvider->getRolesByIdentifiers($user['roles'] ?? []);

        $context->setRoles(empty($roles) === false ? $roles : null);
    }
}
