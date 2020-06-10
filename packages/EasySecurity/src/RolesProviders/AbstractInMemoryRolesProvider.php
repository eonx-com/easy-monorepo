<?php

declare(strict_types=1);

namespace EonX\EasySecurity\RolesProviders;

use EonX\EasySecurity\Interfaces\Authorization\RoleInterface;
use EonX\EasySecurity\Interfaces\RolesProviderInterface;

/**
 * @deprecated Since 2.4, will be removed in 3.0. Use \EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface
 *     instead.
 */
abstract class AbstractInMemoryRolesProvider implements RolesProviderInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    private $roles;

    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    public function getRoles(): array
    {
        if ($this->roles !== null) {
            return $this->roles;
        }

        return $this->roles = \array_filter($this->initRoles(), static function ($role): bool {
            return $role instanceof RoleInterface;
        });
    }

    /**
     * @param string|string[] $identifiers
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRolesByIdentifiers($identifiers): array
    {
        if (empty($identifiers)) {
            return [];
        }

        $roles = [];

        foreach ((array)$identifiers as $identifier) {
            foreach ($this->getRoles() as $role) {
                if ($role->getIdentifier() === $identifier) {
                    $roles[] = $role;
                }
            }
        }

        return $roles;
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    abstract protected function initRoles(): array;
}
