<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

interface RolesProviderInterface
{
    /**
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * @param string|string[] $identifiers
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRolesByIdentifiers($identifiers): array;
}
