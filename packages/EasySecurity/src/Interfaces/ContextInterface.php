<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;

interface ContextInterface
{
    public const JWT_MANAGE_CLAIM = 'https://eonx.com/user';

    /**
     * Add permissions to existing ones.
     *
     * @param string|string[]|\EonX\EasySecurity\Interfaces\PermissionInterface|\EonX\EasySecurity\Interfaces\PermissionInterface[] $permissions
     *
     * @return void
     */
    public function addPermissions($permissions): void;

    /**
     * Add roles to existing ones.
     *
     * @param string|string[]|\EonX\EasySecurity\Interfaces\RoleInterface|\EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     *
     * @return void
     */
    public function addRoles($roles): void;

    /**
     * Get permissions.
     *
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissions(): array;

    /**
     * Get provider.
     *
     * @return null|\EonX\EasySecurity\Interfaces\ProviderInterface
     */
    public function getProvider(): ?ProviderInterface;

    /**
     * Get provider or fail.
     *
     * @return \EonX\EasySecurity\Interfaces\ProviderInterface
     *
     * @throws \EonX\EasySecurity\Exceptions\NoProviderInContextException
     */
    public function getProviderOrFail(): ProviderInterface;

    /**
     * Get roles.
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * Get token.
     *
     * @return null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface
     */
    public function getToken(): ?EasyApiTokenInterface;

    /**
     * Get user.
     *
     * @return null|\EonX\EasySecurity\Interfaces\UserInterface
     */
    public function getUser(): ?UserInterface;

    /**
     * Get user or fail.
     *
     * @return \EonX\EasySecurity\Interfaces\UserInterface
     *
     * @throws \EonX\EasySecurity\Exceptions\NoUserInContextException
     */
    public function getUserOrFail(): UserInterface;

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

    /**
     * Replace existing permissions with given ones.
     *
     * @param string|string[]|\EonX\EasySecurity\Interfaces\PermissionInterface|\EonX\EasySecurity\Interfaces\PermissionInterface[] $permissions
     *
     * @return void
     */
    public function setPermissions($permissions): void;

    /**
     * Set provider.
     *
     * @param null|\EonX\EasySecurity\Interfaces\ProviderInterface $provider
     *
     * @return void
     */
    public function setProvider(?ProviderInterface $provider = null): void;

    /**
     * Replace existing roles with given ones.
     *
     * @param string|string[]|\EonX\EasySecurity\Interfaces\RoleInterface|\EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     *
     * @return void
     */
    public function setRoles($roles): void;

    /**
     * Set token.
     *
     * @param null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface $token
     *
     * @return void
     */
    public function setToken(?EasyApiTokenInterface $token = null): void;

    /**
     * Set user.
     *
     * @param null|\EonX\EasySecurity\Interfaces\UserInterface $user
     *
     * @return void
     */
    public function setUser(?UserInterface $user = null): void;
}
