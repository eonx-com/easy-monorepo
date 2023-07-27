<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

use EonX\EasyApiToken\Interfaces\ApiTokenInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\Authorization\PermissionInterface;
use EonX\EasySecurity\Interfaces\Authorization\RoleInterface;

interface SecurityContextInterface
{
    /**
     * @param string|\EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]|string[] $permissions
     */
    public function addPermissions(array|string $permissions): void;

    /**
     * @param string|\EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]|string[] $roles
     */
    public function addRoles(array|string $roles): void;

    public function getAuthorizationMatrix(): AuthorizationMatrixInterface;

    public function getPermission(string $identifier): ?PermissionInterface;

    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    public function getPermissions(): array;

    public function getProvider(): ?ProviderInterface;

    public function getProviderOrFail(): ProviderInterface;

    public function getRole(string $identifier): ?RoleInterface;

    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    public function getRoles(): array;

    public function getToken(): ?ApiTokenInterface;

    public function getUser(): ?UserInterface;

    public function getUserOrFail(): UserInterface;

    public function hasPermission(string $permission): bool;

    public function hasRole(string $role): bool;

    public function setAuthorizationMatrix(AuthorizationMatrixInterface $authorizationMatrix): void;

    /**
     * @param string|\EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]|string[]|null $permissions
     */
    public function setPermissions(array|string|null $permissions): void;

    public function setProvider(?ProviderInterface $provider = null): void;

    /**
     * @param string|\EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]|string[] $roles
     */
    public function setRoles(array|string $roles): void;

    public function setToken(?ApiTokenInterface $token = null): void;

    public function setUser(?UserInterface $user = null): void;
}
