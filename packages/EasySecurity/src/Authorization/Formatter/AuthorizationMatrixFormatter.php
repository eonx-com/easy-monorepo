<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization\Formatter;

use EonX\EasySecurity\Authorization\ValueObject\Permission;
use EonX\EasySecurity\Authorization\ValueObject\Role;

final class AuthorizationMatrixFormatter
{
    /**
     * @param string[]|\EonX\EasySecurity\Authorization\ValueObject\Permission[] $permissions
     *
     * @return \EonX\EasySecurity\Authorization\ValueObject\Permission[]
     */
    public static function formatPermissions(array $permissions): array
    {
        $filter = static fn (
            $permission,
        ): bool => \is_string($permission) || $permission instanceof Permission;

        $map = static fn (
            $permission,
        ): Permission => \is_string($permission) ? new Permission($permission) : $permission;

        return \array_map($map, \array_filter($permissions, $filter));
    }

    /**
     * @param string[]|\EonX\EasySecurity\Authorization\ValueObject\Role[] $roles
     *
     * @return \EonX\EasySecurity\Authorization\ValueObject\Role[]
     */
    public static function formatRoles(array $roles): array
    {
        $filter = static fn ($role): bool => \is_string($role) || $role instanceof Role;
        $map = static fn ($role): Role => \is_string($role) ? new Role($role, []) : $role;

        return \array_map($map, \array_filter($roles, $filter));
    }

    /**
     * @param \EonX\EasySecurity\Authorization\ValueObject\Role[] $roles
     *
     * @return string[]
     */
    public static function formatRolesToIdentifiers(array $roles): array
    {
        $filter = static fn ($role): bool => $role instanceof Role;
        $map = static fn (Role $role): string => $role->getIdentifier();

        return \array_map($map, \array_filter($roles, $filter));
    }
}
