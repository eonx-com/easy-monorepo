<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Context;

use EonX\EasyApiToken\Common\ValueObject\ApiTokenInterface;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProviderInterface;
use EonX\EasySecurity\Authorization\ValueObject\PermissionInterface;
use EonX\EasySecurity\Authorization\ValueObject\RoleInterface;
use EonX\EasySecurity\Common\Entity\ProviderInterface;
use EonX\EasySecurity\Common\Entity\UserInterface;

interface SecurityContextInterface
{
    /**
     * @param string|\EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]|string[] $permissions
     */
    public function addPermissions(array|string $permissions): void;

    /**
     * @param string|\EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]|string[] $roles
     */
    public function addRoles(array|string $roles): void;

    public function getAuthorizationMatrix(): AuthorizationMatrixProviderInterface;

    public function getPermission(string $identifier): ?PermissionInterface;

    /**
     * @return \EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]
     */
    public function getPermissions(): array;

    public function getProvider(): ?ProviderInterface;

    public function getProviderOrFail(): ProviderInterface;

    public function getRole(string $identifier): ?RoleInterface;

    /**
     * @return \EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]
     */
    public function getRoles(): array;

    public function getToken(): ?ApiTokenInterface;

    public function getUser(): ?UserInterface;

    public function getUserOrFail(): UserInterface;

    public function hasPermission(string $permission): bool;

    public function hasRole(string $role): bool;

    public function setAuthorizationMatrix(AuthorizationMatrixProviderInterface $authorizationMatrix): void;

    /**
     * @param string|\EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]|string[]|null $permissions
     */
    public function setPermissions(array|string|null $permissions): void;

    public function setProvider(?ProviderInterface $provider = null): void;

    /**
     * @param string|\EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]|string[] $roles
     */
    public function setRoles(array|string $roles): void;

    public function setToken(?ApiTokenInterface $token = null): void;

    public function setUser(?UserInterface $user = null): void;
}
