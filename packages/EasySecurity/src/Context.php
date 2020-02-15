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
     * Add permissions.
     *
     * @param \EonX\EasySecurity\Interfaces\PermissionInterface|\EonX\EasySecurity\Interfaces\PermissionInterface[]|string|string[] $permissions
     *
     * @return void
     */
    public function addPermissions($permissions): void
    {
        $this->cachePermissions = null;

        foreach ($this->transformPermissions((array)$permissions) as $permission) {
            $this->permissions[$permission->getIdentifier()] = $permission;
        }
    }

    /**
     * Add roles.
     *
     * @param \EonX\EasySecurity\Interfaces\RoleInterface|\EonX\EasySecurity\Interfaces\RoleInterface[]|string|string[] $roles
     *
     * @return void
     */
    public function addRoles($roles): void
    {
        $this->cachePermissions = null;

        foreach ($this->transformRoles((array)$roles) as $role) {
            $this->roles[$role->getIdentifier()] = $role;
        }
    }

    /**
     * Get permissions.
     *
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

    /**
     * Get provider.
     *
     * @return null|\EonX\EasySecurity\Interfaces\ProviderInterface
     */
    public function getProvider(): ?ProviderInterface
    {
        return $this->provider;
    }

    /**
     * Get provider or fail.
     *
     * @return \EonX\EasySecurity\Interfaces\ProviderInterface
     *
     * @throws \EonX\EasySecurity\Exceptions\NoProviderInContextException
     */
    public function getProviderOrFail(): ProviderInterface
    {
        if ($this->provider !== null) {
            return $this->provider;
        }

        throw new NoProviderInContextException('No provider in context');
    }

    /**
     * Get roles.
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles ?? [];
    }

    /**
     * Get token.
     *
     * @return null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    public function getToken(): ?EasyApiTokenInterface
    {
        return $this->token;
    }

    /**
     * Get user.
     *
     * @return null|\EonX\EasySecurity\Interfaces\UserInterface
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * Get user or fail.
     *
     * @return \EonX\EasySecurity\Interfaces\UserInterface
     *
     * @throws \EonX\EasySecurity\Exceptions\NoUserInContextException
     */
    public function getUserOrFail(): UserInterface
    {
        if ($this->user !== null) {
            return $this->user;
        }

        throw new NoUserInContextException('No user in context');
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
     * Replace existing permissions with given ones.
     *
     * @param string|string[]|\EonX\EasySecurity\Interfaces\PermissionInterface|\EonX\EasySecurity\Interfaces\PermissionInterface[] $permissions
     *
     * @return void
     */
    public function setPermissions($permissions): void
    {
        $this->cachePermissions = null;

        $this->permissions = $this->transformPermissions((array)$permissions);
    }

    /**
     * Set provider.
     *
     * @param null|\EonX\EasySecurity\Interfaces\ProviderInterface $provider
     *
     * @return void
     */
    public function setProvider(?ProviderInterface $provider = null): void
    {
        $this->provider = $provider;
    }

    /**
     * Replace existing roles with given ones.
     *
     * @param string|string[]|\EonX\EasySecurity\Interfaces\RoleInterface|\EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     *
     * @return void
     */
    public function setRoles($roles): void
    {
        $this->cachePermissions = null;
        $this->roles = [];

        foreach ($this->transformRoles((array)$roles) as $role) {
            $this->roles[$role->getIdentifier()] = $role;
        }
    }

    /**
     * Set token.
     *
     * @param null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface $token
     *
     * @return void
     */
    public function setToken(?EasyApiTokenInterface $token = null): void
    {
        $this->token = $token;
    }

    /**
     * Set user.
     *
     * @param null|\EonX\EasySecurity\Interfaces\UserInterface $user
     *
     * @return void
     */
    public function setUser(?UserInterface $user = null): void
    {
        $this->user = $user;
    }

    /**
     * Transform given permissions to permission instances.
     *
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
     * Transform given roles to role instances.
     *
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
