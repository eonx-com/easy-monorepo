<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Provider;

use EonX\EasySecurity\Authorization\Provider\RolesProviderInterface;

final class AuthorizationRolesProviderStub implements RolesProviderInterface
{
    /**
     * @param string[]|\EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]|null $roles
     */
    public function __construct(
        private ?array $roles = null,
    ) {
    }

    /**
     * @return string[]|\EonX\EasySecurity\Authorization\ValueObject\RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles ?? [];
    }
}