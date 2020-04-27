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
     * @param null|\EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface[] $rolesProviders
     * @param null|\EonX\EasySecurity\Interfaces\Authorization\PermissionsProviderInterface[] $permissionsProviders
     */
    public function __construct(?array $rolesProviders = null, ?array $permissionsProviders = null)
    {
        $this->rolesProviders = $rolesProviders ?? [];
        $this->permissionsProviders = $permissionsProviders ?? [];
    }

    public function create(): AuthorizationMatrixInterface
    {
        $matrix = new AuthorizationMatrix();

        foreach ($this->rolesProviders as $rolesProvider) {
            $matrix->addRoles($rolesProvider->getRoles());
        }

        foreach ($this->permissionsProviders as $permissionsProvider) {
            $matrix->addPermissions($permissionsProvider->getPermissions());
        }

        return $matrix;
    }
}
