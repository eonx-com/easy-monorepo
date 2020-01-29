<?php
declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\RoleInterface;

final class Context implements ContextInterface
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
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @inheritDoc
     */
    public function hasPermission(string $permission): bool
    {
        return isset($this->getPermissions()[$permission]);
    }

    /**
     * @inheritDoc
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
