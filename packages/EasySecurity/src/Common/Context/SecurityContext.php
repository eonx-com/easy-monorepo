<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Common\Context;

use EonX\EasyApiToken\Common\ValueObject\ApiTokenInterface;
use EonX\EasySecurity\Authorization\Formatter\AuthorizationMatrixFormatter;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProviderInterface;
use EonX\EasySecurity\Authorization\ValueObject\PermissionInterface;
use EonX\EasySecurity\Authorization\ValueObject\RoleInterface;
use EonX\EasySecurity\Common\Entity\ProviderInterface;
use EonX\EasySecurity\Common\Entity\UserInterface;
use EonX\EasySecurity\Common\Exception\NoProviderInContextException;
use EonX\EasySecurity\Common\Exception\NoUserInContextException;

/**
 * This class is not final to allow apps to extend it.
 */
class SecurityContext implements SecurityContextInterface
{
    private AuthorizationMatrixProviderInterface $authorizationMatrix;

    /**
     * @var \EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]|null
     */
    private ?array $cachePermissions = null;

    /**
     * @var \EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]|null
     */
    private ?array $overridePermissions = null;

    /**
     * @var \EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]|null
     */
    private ?array $permissions = null;

    private ?ProviderInterface $provider = null;

    /**
     * @var \EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]|null
     */
    private ?array $roles = null;

    private ?ApiTokenInterface $token = null;

    private ?UserInterface $user = null;

    /**
     * @param string|\EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]|string[] $permissions
     */
    public function addPermissions(array|string $permissions): void
    {
        $this->cachePermissions = null;

        foreach (AuthorizationMatrixFormatter::formatPermissions((array)$permissions) as $permission) {
            $this->permissions[$permission->getIdentifier()] = $permission;
        }
    }

    /**
     * @param string|\EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]|string[] $roles
     */
    public function addRoles(array|string $roles): void
    {
        $this->cachePermissions = null;

        $roleIdentifiers = AuthorizationMatrixFormatter::formatRolesToIdentifiers(
            AuthorizationMatrixFormatter::formatRoles((array)$roles)
        );

        foreach ($this->authorizationMatrix->getRolesByIdentifiers($roleIdentifiers) as $role) {
            $this->roles[$role->getIdentifier()] = $role;
        }
    }

    public function getAuthorizationMatrix(): AuthorizationMatrixProviderInterface
    {
        return $this->authorizationMatrix;
    }

    public function getPermission(string $identifier): ?PermissionInterface
    {
        return $this->getPermissions()[$identifier] ?? null;
    }

    /**
     * @return \EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]
     */
    public function getPermissions(): array
    {
        // If setPermissions() is called it overrides any other permissions
        if ($this->overridePermissions !== null) {
            return $this->overridePermissions;
        }

        if ($this->cachePermissions !== null) {
            return $this->cachePermissions;
        }

        $cachePermissions = [];

        foreach ($this->getRoles() as $role) {
            foreach ($role->getPermissions() as $permission) {
                $cachePermissions[$permission->getIdentifier()] = $permission;
            }
        }

        foreach ($this->permissions ?? [] as $permission) {
            $cachePermissions[$permission->getIdentifier()] = $permission;
        }

        $this->cachePermissions = $cachePermissions;

        return $this->cachePermissions;
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

    public function getRole(string $identifier): ?RoleInterface
    {
        return $this->getRoles()[$identifier] ?? null;
    }

    /**
     * @return \EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles ?? [];
    }

    public function getToken(): ?ApiTokenInterface
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
        return isset($this->getRoles()[$role]);
    }

    public function setAuthorizationMatrix(AuthorizationMatrixProviderInterface $authorizationMatrix): void
    {
        $this->authorizationMatrix = $authorizationMatrix;
    }

    /**
     * @param string|\EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]|string[]|null $permissions
     */
    public function setPermissions(array|string|null $permissions): void
    {
        // Allow to remove the permissions override
        if ($permissions === null) {
            $this->overridePermissions = null;

            return;
        }

        $overridePermissions = [];

        foreach (AuthorizationMatrixFormatter::formatPermissions((array)$permissions) as $permission) {
            $overridePermissions[$permission->getIdentifier()] = $permission;
        }

        $this->overridePermissions = $overridePermissions;
    }

    public function setProvider(?ProviderInterface $provider = null): void
    {
        $this->provider = $provider;
    }

    /**
     * @param string|\EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]|string[] $roles
     */
    public function setRoles(array|string $roles): void
    {
        $this->cachePermissions = null;
        $this->roles = [];

        $this->addRoles($roles);
    }

    public function setToken(?ApiTokenInterface $token = null): void
    {
        $this->token = $token;
    }

    public function setUser(?UserInterface $user = null): void
    {
        $this->user = $user;
    }
}
