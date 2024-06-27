<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization\Factory;

use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProvider;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProviderInterface;
use EonX\EasySecurity\Authorization\Provider\PermissionsProviderInterface;
use EonX\EasySecurity\Authorization\Provider\RolesProviderInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;

final class AuthorizationMatrixFactory implements AuthorizationMatrixFactoryInterface
{
    /**
     * @var \EonX\EasySecurity\Authorization\Provider\PermissionsProviderInterface[]
     */
    private array $permissionsProviders;

    /**
     * @var \EonX\EasySecurity\Authorization\Provider\RolesProviderInterface[]
     */
    private array $rolesProviders;

    /**
     * @param iterable<\EonX\EasySecurity\Authorization\Provider\RolesProviderInterface> $rolesProviders
     * @param iterable<\EonX\EasySecurity\Authorization\Provider\PermissionsProviderInterface> $permissionsProviders
     */
    public function __construct(iterable $rolesProviders, iterable $permissionsProviders)
    {
        $this->rolesProviders = $this->filterProviders($rolesProviders, RolesProviderInterface::class);
        $this->permissionsProviders = $this->filterProviders(
            $permissionsProviders,
            PermissionsProviderInterface::class
        );
    }

    public function create(): AuthorizationMatrixProviderInterface
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

        return new AuthorizationMatrixProvider($roles, $permissions);
    }

    /**
     * @return \EonX\EasySecurity\Authorization\Provider\PermissionsProviderInterface[]
     */
    public function getPermissionsProviders(): array
    {
        return $this->permissionsProviders;
    }

    /**
     * @return \EonX\EasySecurity\Authorization\Provider\RolesProviderInterface[]
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
