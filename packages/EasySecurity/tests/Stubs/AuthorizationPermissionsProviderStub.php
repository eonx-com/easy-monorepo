<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\Authorization\PermissionsProviderInterface;

final class AuthorizationPermissionsProviderStub implements PermissionsProviderInterface
{
    /**
     * @var null|string[]|\EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    private $permissions;

    /**
     * @param null|string[]|\EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[] $permissions
     */
    public function __construct(?array $permissions = null)
    {
        $this->permissions = $permissions;
    }

    /**
     * @return string[]|\EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    public function getPermissions(): array
    {
        return $this->permissions ?? [];
    }
}
