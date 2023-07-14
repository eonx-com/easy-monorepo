<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Interfaces\Authorization\RolesProviderInterface;

final class AuthorizationRolesProviderStub implements RolesProviderInterface
{
    /**
     * @param null|string[]|\EonX\EasySecurity\Interfaces\Authorization\RoleInterface[] $roles
     */
    public function __construct(
        private ?array $roles = null,
    ) {
    }

    /**
     * @return string[]|\EonX\EasySecurity\Interfaces\Authorization\RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles ?? [];
    }
}
