<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Resolvers;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;
use EonX\EasySecurity\Interfaces\RolesProviderInterface;

final class RolesFromJwtDataResolver extends AbstractContextDataResolver
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
     * Resolve context data.
     *
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface $data
     *
     * @return \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface
     */
    public function resolve(ContextResolvingDataInterface $data): ContextResolvingDataInterface
    {
        $token = $data->getApiToken();

        // Work only for JWT
        if ($token instanceof JwtEasyApiTokenInterface === false) {
            return $data;
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */

        $user = $this->getClaimSafely($token, ContextInterface::JWT_MANAGE_CLAIM, []);
        $roles = $this->rolesProvider->getRolesByIdentifiers($user['roles'] ?? []);

        $data->setRoles(empty($roles) === false ? $roles : null);

        return $data;
    }
}
