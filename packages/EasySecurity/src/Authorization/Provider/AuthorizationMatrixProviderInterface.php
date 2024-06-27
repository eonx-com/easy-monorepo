<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization\Provider;

interface AuthorizationMatrixProviderInterface
{
    /**
     * @return \EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]
     */
    public function getPermissions(): array;

    /**
     * @param string[] $identifiers
     *
     * @return \EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]
     */
    public function getPermissionsByIdentifiers(array $identifiers): array;

    /**
     * @return \EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * @param string[] $identifiers
     *
     * @return \EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]
     */
    public function getRolesByIdentifiers(array $identifiers): array;

    public function isPermission(string $permission): bool;

    public function isRole(string $role): bool;
}
