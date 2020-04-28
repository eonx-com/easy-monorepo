<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization\Helpers;

use EonX\EasySecurity\Interfaces\PermissionInterface;
use EonX\EasySecurity\Interfaces\RoleInterface;
use EonX\EasySecurity\Permission;
use EonX\EasySecurity\Role;

final class AuthorizationMatrixFormatter
{
    /**
     * @param string[]|\EonX\EasySecurity\Interfaces\PermissionInterface[] $permissions
     *
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public static function formatPermissions(array $permissions): array
    {
        $filter = static function ($permission): bool {
            return \is_string($permission) || $permission instanceof PermissionInterface;
        };
        $map = static function ($permission): PermissionInterface {
            return \is_string($permission) ? new Permission($permission) : $permission;
        };

        return \array_map($map, \array_filter($permissions, $filter));
    }

    /**
     * @param string[]|\EonX\EasySecurity\Interfaces\RoleInterface[] $permissions
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public static function formatRoles(array $roles): array
    {
        $filter = static function ($role): bool {
            return \is_string($role) || $role instanceof RoleInterface;
        };
        $map = static function ($role): RoleInterface {
            return \is_string($role) ? new Role($role, []) : $role;
        };

        return \array_map($map, \array_filter($roles, $filter));
    }
}
