<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Factory;

use EonX\EasySecurity\Authorization\Factory\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProvider;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProviderInterface;
use EonX\EasySecurity\Authorization\ValueObject\Role;

final class AuthorizationMatrixFactoryStub implements AuthorizationMatrixFactoryInterface
{
    private int $calls = 0;

    public function create(): AuthorizationMatrixProviderInterface
    {
        $this->calls++;

        return new AuthorizationMatrixProvider([new Role('role')], []);
    }

    public function getCalls(): int
    {
        return $this->calls;
    }
}
