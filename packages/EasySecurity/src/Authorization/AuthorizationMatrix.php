<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization;

use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\PermissionInterface;
use EonX\EasySecurity\Interfaces\RoleInterface;
use EonX\EasySecurity\Permission;
use EonX\EasySecurity\Role;

final class AuthorizationMatrix implements AuthorizationMatrixInterface
{
    /**
     * @var null|\EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    private $cachePermissions;

    /**
     * @var \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    private $permissions = [];

    /**
     * @var \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    private $roles = [];

    /**
     * @param string[]|\EonX\EasySecurity\Interfaces\PermissionInterface[] $permissions
     */
    public function addPermissions(array $permissions): AuthorizationMatrixInterface
    {
        $this->reset();

        foreach ($this->transformPermissions($permissions) as $permission) {
            $this->permissions[$permission->getIdentifier()] = $permission;
        }

        return $this;
    }

    /**
     * @param string[]|\EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     */
    public function addRoles(array $roles): AuthorizationMatrixInterface
    {
        $this->reset();

        foreach ($this->transformRoles($roles) as $role) {
            $this->roles[$role->getIdentifier()] = $role;
        }

        return $this;
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissions(): array
    {
        if ($this->cachePermissions !== null) {
            return $this->cachePermissions;
        }

        $permissions = [];

        foreach ($this->getRoles() as $role) {
            foreach ($role->getPermissions() as $permission) {
                $permissions[$permission->getIdentifier()] = $permission;
            }
        }

        foreach ($this->permissions as $permission) {
            $permissions[$permission->getIdentifier()] = $permission;
        }

        return $this->cachePermissions = $permissions;
    }

    /**
     * @param string[] $identifiers
     *
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissionsByIdentifiers(array $identifiers): array
    {
        $permissions = [];

        foreach ($this->getPermissions() as $identifier => $permission) {
            if (\in_array($identifier, $identifiers, true) === false) {
                continue;
            }

            $permissions[] = $permission;
        }

        return $permissions;
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string[] $identifiers
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRolesByIdentifiers(array $identifiers): array
    {
        $roles = [];

        foreach ($this->getRoles() as $identifier => $role) {
            if (\in_array($identifier, $identifiers, true) === false) {
                continue;
            }

            $roles[] = $role;
        }

        return $roles;
    }

    public function isPermission(string $permission): bool
    {
        return isset($this->getPermissions()[$permission]);
    }

    public function isRole(string $role): bool
    {
        return isset($this->getRoles()[$role]);
    }

    /**
     * @param string[]|\EonX\EasySecurity\Interfaces\PermissionInterface[] $permissions
     *
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function transformPermissions(array $permissions): array
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
    public function transformRoles(array $roles): array
    {
        $filter = static function ($role): bool {
            return \is_string($role) || $role instanceof RoleInterface;
        };
        $map = static function ($role): RoleInterface {
            return \is_string($role) ? new Role($role, []) : $role;
        };

        return \array_map($map, \array_filter($roles, $filter));
    }

    private function reset(): void
    {
        $this->cachePermissions = null;
    }
}
