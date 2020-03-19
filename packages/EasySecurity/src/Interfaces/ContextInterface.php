<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;

interface ContextInterface
{
    /**
     * @param string|string[]|\EonX\EasySecurity\Interfaces\PermissionInterface|\EonX\EasySecurity\Interfaces\PermissionInterface[] $permissions
     */
    public function addPermissions($permissions): void;

    /**
     * @param string|string[]|\EonX\EasySecurity\Interfaces\RoleInterface|\EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     */
    public function addRoles($roles): void;

    /**
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissions(): array;

    public function getProvider(): ?ProviderInterface;

    public function getProviderOrFail(): ProviderInterface;

    /**
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): array;

    public function getToken(): ?EasyApiTokenInterface;

    public function getUser(): ?UserInterface;

    public function getUserOrFail(): UserInterface;

    public function hasPermission(string $permission): bool;

    public function hasRole(string $role): bool;

    /**
     * @param string|string[]|\EonX\EasySecurity\Interfaces\PermissionInterface|\EonX\EasySecurity\Interfaces\PermissionInterface[] $permissions
     */
    public function setPermissions($permissions): void;

    public function setProvider(?ProviderInterface $provider = null): void;

    /**
     * @param string|string[]|\EonX\EasySecurity\Interfaces\RoleInterface|\EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     */
    public function setRoles($roles): void;

    public function setToken(?EasyApiTokenInterface $token = null): void;

    public function setUser(?UserInterface $user = null): void;
}
