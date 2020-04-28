<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Authorization;

interface AuthorizationMatrixInterface
{
    /**
     * @param string[]|\EonX\EasySecurity\Interfaces\PermissionInterface[] $permissions
     */
    public function addPermissions(array $permissions): self;

    /**
     * @param string[]|\EonX\EasySecurity\Interfaces\RoleInterface[] $roles
     */
    public function addRoles(array $roles): self;

    /**
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissions(): array;

    /**
     * @param string[] $identifiers
     *
     * @return \EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissionsByIdentifiers(array $identifiers): array;

    /**
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * @param string[] $identifiers
     *
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    public function getRolesByIdentifiers(array $identifiers): array;

    public function isPermission(string $permission): bool;

    public function isRole(string $role): bool;
}
