<?php
declare(strict_types=1);

namespace EonX\EasySecurity\RolesProviders;

use EonX\EasySecurity\Interfaces\RoleInterface;
use EonX\EasySecurity\Interfaces\RolesProviderInterface;

abstract class AbstractInMemoryRolesProvider implements RolesProviderInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    private $roles;

    /**
     * Get roles.
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
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
     * Get list of roles for given identifiers.
     *
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
     * Init roles.
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    abstract protected function initRoles(): array;
}
