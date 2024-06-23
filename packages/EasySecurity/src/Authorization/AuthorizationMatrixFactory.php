<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization;

use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Interfaces\Authorization\PermissionsProviderInterface;
use EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface;
use EonX\EasyUtils\Common\Helper\CollectorHelper;

final class AuthorizationMatrixFactory implements AuthorizationMatrixFactoryInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\PermissionsProviderInterface[]
     */
    private array $permissionsProviders;

    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface[]
     */
    private array $rolesProviders;

    /**
     * @param iterable<\EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface> $rolesProviders
     * @param iterable<\EonX\EasySecurity\Interfaces\Authorization\PermissionsProviderInterface> $permissionsProviders
     */
    public function __construct(iterable $rolesProviders, iterable $permissionsProviders)
    {
        $this->rolesProviders = $this->filterProviders($rolesProviders, RolesProviderInterface::class);
        $this->permissionsProviders = $this->filterProviders(
            $permissionsProviders,
            PermissionsProviderInterface::class
        );
    }

    public function create(): AuthorizationMatrixInterface
    {
        $permissions = [];
        $roles = [];

        foreach ($this->rolesProviders as $rolesProvider) {
            foreach ($rolesProvider->getRoles() as $role) {
                $roles[] = $role;
            }
        }

        foreach ($this->permissionsProviders as $permissionsProvider) {
            foreach ($permissionsProvider->getPermissions() as $permission) {
                $permissions[] = $permission;
            }
        }

        return new AuthorizationMatrix($roles, $permissions);
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\PermissionsProviderInterface[]
     */
    public function getPermissionsProviders(): array
    {
        return $this->permissionsProviders;
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface[]
     */
    public function getRolesProviders(): array
    {
        return $this->rolesProviders;
    }

    /**
     * @param class-string $class
     */
    private function filterProviders(iterable $providers, string $class): array
    {
        return CollectorHelper::filterByClassAsArray($providers, $class);
    }
}
