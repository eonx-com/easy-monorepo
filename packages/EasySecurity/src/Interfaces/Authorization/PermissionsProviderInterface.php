<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Authorization;

interface PermissionsProviderInterface
{
    /**
     * @return string[]|\EonX\EasySecurity\Interfaces\PermissionInterface[]
     */
    public function getPermissions(): array;
}
