<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization\Formatter;

use EonX\EasySecurity\Authorization\ValueObject\Permission;
use EonX\EasySecurity\Authorization\ValueObject\PermissionInterface;
use EonX\EasySecurity\Authorization\ValueObject\Role;
use EonX\EasySecurity\Authorization\ValueObject\RoleInterface;

final class AuthorizationMatrixFormatter
{
    /**
     * @param string[]|\EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[] $permissions
     *
     * @return \EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]
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
     * @param string[]|\EonX\EasySecurity\Authorization\ValueObject\RoleInterface[] $roles
     *
     * @return \EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]
     */
    public static function formatRoles(array $roles): array
    {
        $filter = static fn ($role): bool => \is_string($role) || $role instanceof RoleInterface;
        $map = static fn ($role): RoleInterface => \is_string($role) ? new Role($role, []) : $role;

        return \array_map($map, \array_filter($roles, $filter));
    }

    /**
     * @param \EonX\EasySecurity\Authorization\ValueObject\RoleInterface[] $roles
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
