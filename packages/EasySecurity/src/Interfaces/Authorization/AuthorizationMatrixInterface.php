<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Authorization;

interface AuthorizationMatrixInterface
{
    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    public function getPermissions(): array;

    /**
     * @param string[] $identifiers
     *
     * @return \EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    public function getPermissionsByIdentifiers(array $identifiers): array;

    /**
     * @return \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * @param string[] $identifiers
     *
     * @return \EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    public function getRolesByIdentifiers(array $identifiers): array;

    public function isPermission(string $permission): bool;

    public function isRole(string $role): bool;
}
