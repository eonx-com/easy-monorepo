<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Provider;

use EonX\EasySecurity\Authorization\Provider\PermissionsProviderInterface;

final readonly class AuthorizationPermissionsProviderStub implements PermissionsProviderInterface
{
    /**
     * @param string[]|\EonX\EasySecurity\Authorization\ValueObject\Permission[]|null $permissions
     */
    public function __construct(
        private ?array $permissions = null,
    ) {
    }

    /**
     * @return string[]|\EonX\EasySecurity\Authorization\ValueObject\Permission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions ?? [];
    }
}
