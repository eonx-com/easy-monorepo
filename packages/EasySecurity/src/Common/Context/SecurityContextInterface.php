<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Context;

use EonX\EasyApiToken\Common\ValueObject\ApiTokenInterface;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProviderInterface;
use EonX\EasySecurity\Authorization\ValueObject\Permission;
use EonX\EasySecurity\Authorization\ValueObject\Role;
use EonX\EasySecurity\Common\Entity\ProviderInterface;
use EonX\EasySecurity\Common\Entity\UserInterface;

interface SecurityContextInterface
{
    /**
     * @param string|\EonX\EasySecurity\Authorization\ValueObject\Permission[]|string[] $permissions
     */
    public function addPermissions(array|string $permissions): void;

    /**
     * @param string|\EonX\EasySecurity\Authorization\ValueObject\Role[]|string[] $roles
     */
    public function addRoles(array|string $roles): void;

    public function getAuthorizationMatrix(): AuthorizationMatrixProviderInterface;

    public function getPermission(string $identifier): ?Permission;

    /**
     * @return \EonX\EasySecurity\Authorization\ValueObject\Permission[]
     */
    public function getPermissions(): array;

    public function getProvider(): ?ProviderInterface;

    public function getProviderOrFail(): ProviderInterface;

    public function getRole(string $identifier): ?Role;

    /**
     * @return \EonX\EasySecurity\Authorization\ValueObject\Role[]
     */
    public function getRoles(): array;

    public function getToken(): ?ApiTokenInterface;

    public function getUser(): ?UserInterface;

    public function getUserOrFail(): UserInterface;

    public function hasPermission(string $permission): bool;

    public function hasRole(string $role): bool;

    public function setAuthorizationMatrix(AuthorizationMatrixProviderInterface $authorizationMatrix): void;

    /**
     * @param string|\EonX\EasySecurity\Authorization\ValueObject\Permission[]|string[]|null $permissions
     */
    public function setPermissions(array|string|null $permissions): void;

    public function setProvider(?ProviderInterface $provider = null): void;

    /**
     * @param string|\EonX\EasySecurity\Authorization\ValueObject\Role[]|string[] $roles
     */
    public function setRoles(array|string $roles): void;

    public function setToken(?ApiTokenInterface $token = null): void;

    public function setUser(?UserInterface $user = null): void;
}
