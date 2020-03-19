<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasySecurity\Exceptions\NoProviderInContextException;
use EonX\EasySecurity\Exceptions\NoUserInContextException;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\PermissionInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\RoleInterface;
use EonX\EasySecurity\Interfaces\UserInterface;

/**
 * Class not final because each app MUST extend it and define their own return types.
 */
class Context implements ContextInterface
{
    /**
     * @var null|\EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    private $cachePermissions;

    /**
     * @var \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    private $permissions;

    /**
     * @var null|\EonX\EasySecurity\Interfaces\ProviderInterface
     */
    private $provider;

    /**
     * @var \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    private $roles;

    /**
     * @var null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    private $token;

    /**
     * @var null|\EonX\EasySecurity\Interfaces\UserInterface
     */
    private $user;

    /**
     * @param \EonX\EasySecurity\Interfaces\PermissionInterface|\EonX\EasySecurity\Interfaces\PermissionInterface[]|string|string[] $permissions
     */
    public function addPermissions($permissions): void
    {
        $this->cachePermissions = null;

        foreach ($this->transformPermissions((array)$permissions) as $permission) {
            $this->permissions[$permission->getIdentifier()] = $permission;
        }
    }

    /**
     * @param \EonX\EasySecurity\Interfaces\RoleInterface|\EonX\EasySecurity\Interfaces\RoleInterface[]|string|string[] $roles
     */
    public function addRoles($roles): void
    {
        $this->cachePermissions = null;

        foreach ($this->transformRoles((array)$roles) as $role) {
            $this->roles[$role->getIdentifier()] = $role;
        }
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissions(): array
    {
        if ($this->cachePermissions !== null) {
            return $this->cachePermissions;
        }

        $cachePermissions = [];

        foreach ($this->roles ?? [] as $role) {
            foreach ($role->getPermissions() as $permission) {
                $cachePermissions[$permission->getIdentifier()] = $permission;
            }
        }

        foreach ($this->permissions ?? [] as $permission) {
            $cachePermissions[$permission->getIdentifier()] = $permission;
        }

        return $this->cachePermissions = $cachePermissions;
    }

    public function getProvider(): ?ProviderInterface
    {
        return $this->provider;
    }

    public function getProviderOrFail(): ProviderInterface
    {
        if ($this->provider !== null) {
            return $this->provider;
        }

        throw new NoProviderInContextException('No provider in context');
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles ?? [];
    }

    public function getToken(): ?EasyApiTokenInterface
    {
        return $this->token;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function getUserOrFail(): UserInterface
    {
        if ($this->user !== null) {
            return $this->user;
        }

        throw new NoUserInContextException('No user in context');
    }

    public function hasPermission(string $permission): bool
    {
        return isset($this->getPermissions()[$permission]);
    }

    public function hasRole(string $role): bool
    {
        return isset($this->roles[$role]);
    }

    /**
     * @param string|string[]|\EonX\EasySecurity\Interfaces\PermissionInterface|\EonX\EasySecurity\Interfaces\PermissionInterface[] $permissions
     */
    public function setPermissions($permissions): void
    {
        $this->cachePermissions = null;

        $this->permissions = $this->transformPermissions((array)$permissions);
    }

    public function setProvider(?ProviderInterface $provider = null): void
    {
        $this->provider = $provider;
    }

    /**
     * @param string|string[]|\EonX\EasySecurity\Interfaces\RoleInterface|\EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     */
    public function setRoles($roles): void
    {
        $this->cachePermissions = null;
        $this->roles = [];

        foreach ($this->transformRoles((array)$roles) as $role) {
            $this->roles[$role->getIdentifier()] = $role;
        }
    }

    public function setToken(?EasyApiTokenInterface $token = null): void
    {
        $this->token = $token;
    }

    public function setUser(?UserInterface $user = null): void
    {
        $this->user = $user;
    }

    /**
     * @param mixed[] $permissions
     *
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    private function transformPermissions(array $permissions): array
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
     * @param mixed[] $roles
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    private function transformRoles(array $roles): array
    {
        $filter = static function ($roles): bool {
            return \is_string($roles) || $roles instanceof RoleInterface;
        };
        $map = static function ($roles): RoleInterface {
            return \is_string($roles) ? new Role($roles, []) : $roles;
        };

        return \array_map($map, \array_filter($roles, $filter));
    }
}
