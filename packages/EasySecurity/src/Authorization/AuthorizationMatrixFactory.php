<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization;

use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;

final class AuthorizationMatrixFactory implements AuthorizationMatrixFactoryInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\PermissionsProviderInterface[]
     */
    private $permissionsProviders;

    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface[]
     */
    private $rolesProviders;

    /**
     * AuthorizationMatrixFactory constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface[] $rolesProviders
     * @param \EonX\EasySecurity\Interfaces\Authorization\PermissionsProviderInterface[] $permissionsProviders
     */
    public function __construct(array $rolesProviders, array $permissionsProviders)
    {
        $this->rolesProviders = $rolesProviders;
        $this->permissionsProviders = $permissionsProviders;
    }

    public function create(): AuthorizationMatrixInterface
    {
        $permissions = [];
        $roles = [];

        foreach ($this->rolesProviders as $rolesProvider) {
            foreach ($rolesProvider->getRoles() as $role) {
                $roles[] = $role;
            }
        }

        foreach ($this->permissionsProviders as $permissionsProvider) {
            foreach ($permissionsProvider->getPermissions() as $permission) {
                $permissions[] = $permission;
            }
        }

        return new AuthorizationMatrix($roles, $permissions);
    }
}
