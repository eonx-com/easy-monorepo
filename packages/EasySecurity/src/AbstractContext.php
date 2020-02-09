<?php
declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\RoleInterface;

abstract class AbstractContext implements ContextInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    private $permissions;

    /**
     * @var \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    private $roles;

    /**
     * Context constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     */
    public function __construct(array $roles)
    {
        $this->initRoles(\array_filter($roles, static function ($role): bool {
            return $role instanceof RoleInterface;
        }));
    }

    /**
     * Get permissions.
     *
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissions(): array
    {
        if ($this->permissions !== null) {
            return $this->permissions;
        }

        $permissions = [];

        foreach ($this->roles as $role) {
            foreach ($role->getPermissions() as $permission) {
                $permissions[$permission->getIdentifier()] = $permission;
            }
        }

        return $this->permissions = $permissions;
    }

    /**
     * Get roles.
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Check if context has given permission.
     *
     * @param string $permission The identifier of the permission
     *
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return isset($this->getPermissions()[$permission]);
    }

    /**
     * Check if context has given role.
     *
     * @param string $role The identifier of the role
     *
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return isset($this->roles[$role]);
    }

    /**
     * Init roles.
     *
     * @param \EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     *
     * @return void
     */
    private function initRoles(array $roles): void
    {
        $indexed = [];

        foreach ($roles as $role) {
            $indexed[$role->getIdentifier()] = $role;
        }

        $this->roles = $indexed;
    }
}
