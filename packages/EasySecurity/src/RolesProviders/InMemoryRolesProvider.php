<?php
declare(strict_types=1);

namespace EonX\EasySecurity\RolesProviders;

use EonX\EasySecurity\Interfaces\RoleInterface;
use EonX\EasySecurity\Interfaces\RolesProviderInterface;

final class InMemoryRolesProvider implements RolesProviderInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    private $roles;

    /**
     * InMemoryRolesProvider constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     */
    public function __construct(array $roles)
    {
        $this->roles = \array_filter($roles, static function ($role): bool {
            return $role instanceof RoleInterface;
        });
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @inheritDoc
     */
    public function getRolesByIdentifiers($identifiers): array
    {
        $roles = [];

        foreach ((array)$identifiers as $identifier) {
            foreach ($this->roles as $role) {
                if ($role->getIdentifier() === $identifier) {
                    $roles[] = $role;
                }
            }
        }

        return $roles;
    }
}
