<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces;

/**
 * @deprecated Since 2.4, will be removed in 3.0. Use \EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface
 *     instead.
 */
interface RolesProviderInterface
{
    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * @param string|string[] $identifiers
     *
     * @return \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    public function getRolesByIdentifiers($identifiers): array;
}
