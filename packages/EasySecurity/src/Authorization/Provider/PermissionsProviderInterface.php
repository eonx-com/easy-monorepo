<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization\Provider;

interface PermissionsProviderInterface
{
    /**
     * @return string[]|\EonX\EasySecurity\Authorization\ValueObject\PermissionInterface[]
     */
    public function getPermissions(): array;
}
