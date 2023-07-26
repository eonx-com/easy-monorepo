<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stubs;

use EonX\EasySecurity\Authorization\AuthorizationMatrix;
use EonX\EasySecurity\Authorization\Role;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixFactoryInterface;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;

final class AuthorizationMatrixFactoryStub implements AuthorizationMatrixFactoryInterface
{
    private int $calls = 0;

    public function create(): AuthorizationMatrixInterface
    {
        $this->calls++;

        return new AuthorizationMatrix([new Role('role')], []);
    }

    public function getCalls(): int
    {
        return $this->calls;
    }
}
