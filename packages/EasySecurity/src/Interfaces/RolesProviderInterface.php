<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface RolesProviderInterface
{
    /**
     * Get roles.
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * Get list of roles for given identifiers.
     *
     * @param string|string[] $identifiers
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRolesByIdentifiers($identifiers): array;
}
