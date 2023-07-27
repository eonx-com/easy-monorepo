<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\Authorization\PermissionsProviderInterface;
use EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface;

final class RolesAndPermissionsProviderStub implements RolesProviderInterface, PermissionsProviderInterface
{
    /**
     * @var string[]|\EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    private array $permissions;

    /**
     * @var string[]|\EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    private array $roles;

    /**
     * @param string[]|\EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]|null $roles
     * @param string[]|\EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]|null $permissions
     */
    public function __construct(?array $roles = null, ?array $permissions = null)
    {
        $this->roles = $roles ?? [];
        $this->permissions = $permissions ?? [];
    }

    /**
     * @return string[]|\EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @return string[]|\EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}
