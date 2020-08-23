<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Modifiers;

use EonX\EasyApiToken\Interfaces\Tokens\JwtInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\JwtClaimFetcherInterface;
use EonX\EasySecurity\Interfaces\RolesProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated Since 2.4, will be removed in 3.0.
 */
final class RolesFromJwtModifier extends AbstractFromJwtContextModifier
{
    /**
     * @var \EonX\EasySecurity\Interfaces\RolesProviderInterface
     */
    private $rolesProvider;

    public function __construct(
        RolesProviderInterface $rolesProvider,
        ?string $jwtClaim = null,
        ?int $priority = null,
        ?JwtClaimFetcherInterface $jwtClaimFetcher = null
    ) {
        $this->rolesProvider = $rolesProvider;

        parent::__construct($jwtClaim, $priority, $jwtClaimFetcher);
    }

    public function modify(ContextInterface $context, Request $request): void
    {
        $token = $context->getToken();

        // Work only for JWT
        if ($token instanceof JwtInterface === false) {
            return;
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtInterface $token */

        $roles = $this->rolesProvider->getRolesByIdentifiers($this->getMainClaim($token)['roles'] ?? []);

        if (empty($roles)) {
            return;
        }

        $context->setRoles($roles);
    }
}
