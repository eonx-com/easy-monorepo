<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;

interface ContextInterface
{
    public const JWT_MANAGE_CLAIM = 'https://eonx.com/user';

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
