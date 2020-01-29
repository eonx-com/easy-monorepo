<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface ContextInterface
{
    /**
     * Get permissions.
     *
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissions(): array;

    /**
     * Get roles.
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * Check if context has given permission.
     *
     * @param string $permission The identifier of the permission
     *
     * @return bool
     */
    public function hasPermission(string $permission): bool;

    /**
     * Check if context has given role.
     *
     * @param string $role The identifier of the role
     *
     * @return bool
     */
    public function hasRole(string $role): bool;
}
