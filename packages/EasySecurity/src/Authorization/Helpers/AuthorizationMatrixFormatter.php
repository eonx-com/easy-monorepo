<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Authorization\Helpers;

use EonX\EasySecurity\Authorization\Permission;
use EonX\EasySecurity\Authorization\Role;
use EonX\EasySecurity\Interfaces\Authorization\PermissionInterface;
use EonX\EasySecurity\Interfaces\Authorization\RoleInterface;

final class AuthorizationMatrixFormatter
{
    /**
     * @param string[]|\EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[] $permissions
     *
     * @return \EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    public static function formatPermissions(array $permissions): array
    {
        $filter = static fn (
            $permission,
        ): bool => \is_string($permission) || $permission instanceof PermissionInterface;

        $map = static fn (
            $permission,
        ): PermissionInterface => \is_string($permission) ? new Permission($permission) : $permission;

        return \array_map($map, \array_filter($permissions, $filter));
    }

    /**
     * @param string[]|\EonX\EasySecurity\Interfaces\Authorization\RoleInterface[] $roles
     *
     * @return \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    public static function formatRoles(array $roles): array
    {
        $filter = static fn ($role): bool => \is_string($role) || $role instanceof RoleInterface;
        $map = static fn ($role): RoleInterface => \is_string($role) ? new Role($role, []) : $role;

        return \array_map($map, \array_filter($roles, $filter));
    }

    /**
     * @param \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[] $roles
     *
     * @return string[]
     */
    public static function formatRolesToIdentifiers(array $roles): array
    {
        $filter = static fn ($role): bool => $role instanceof RoleInterface;
        $map = static fn (RoleInterface $role): string => $role->getIdentifier();

        return \array_map($map, \array_filter($roles, $filter));
    }
}
