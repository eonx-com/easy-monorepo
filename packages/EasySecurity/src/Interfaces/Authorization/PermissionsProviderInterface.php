<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Authorization;

interface PermissionsProviderInterface
{
    /**
     * @return string[]|\EonX\EasySecurity\Interfaces\Authorization\PermissionInterface[]
     */
    public function getPermissions(): array;
}
