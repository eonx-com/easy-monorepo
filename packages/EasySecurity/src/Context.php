<?php
declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasyApiToken\Interfaces\EasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\ProviderInterface;
use EonX\EasySecurity\Interfaces\RoleInterface;
use EonX\EasySecurity\Interfaces\UserInterface;

/**
 * Class not final because each app MUST extend it and define their own return types.
 */
class Context implements ContextInterface
{
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
     * Context constructor.
     *
     * @param null|\EonX\EasyApiToken\Interfaces\EasyApiTokenInterface $token
     * @param null|\EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     * @param null|\EonX\EasySecurity\Interfaces\ProviderInterface $provider
     * @param null|\EonX\EasySecurity\Interfaces\UserInterface $user
     */
    public function __construct(
        ?EasyApiTokenInterface $token = null,
        ?array $roles = null,
        ?ProviderInterface $provider = null,
        ?UserInterface $user = null
    ) {
        $this->initRoles(\array_filter($roles ?? [], static function ($role): bool {
            return $role instanceof RoleInterface;
        }));

        $this->token = $token;
        $this->provider = $provider;
        $this->user = $user;
    }

    /**
     * Get permissions.
     *
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
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
     * Get provider.
     *
     * @return null|\EonX\EasySecurity\Interfaces\ProviderInterface
     */
    public function getProvider(): ?ProviderInterface
    {
        return $this->provider;
    }

    /**
     * Get roles.
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles;
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
