<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Interfaces\Authorization;

interface RolesProviderInterface
{
    /**
     * @return string[]|\EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    public function getRoles(): array;
}
