<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Authorization\Provider;

interface RolesProviderInterface
{
    /**
     * @return string[]|\EonX\EasySecurity\Authorization\ValueObject\Role[]
     */
    public function getRoles(): array;
}
