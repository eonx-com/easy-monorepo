<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Authorization;

use EonX\EasySecurity\Authorization\SymfonyCacheAuthorizationMatrixFactory;
use EonX\EasySecurity\Interfaces\Authorization\AuthorizationMatrixInterface;
use EonX\EasySecurity\Tests\AbstractTestCase;
use EonX\EasySecurity\Tests\Stubs\AuthorizationMatrixFactoryStub;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class SymfonyCacheAuthorizationMatrixFactoryTest extends AbstractTestCase
{
    public function testCacheWorking(): void
    {
        $cache = new ArrayAdapter();
        $stub = new AuthorizationMatrixFactoryStub();
        $factory = new SymfonyCacheAuthorizationMatrixFactory($cache, $stub);

        self::assertInstanceOf(AuthorizationMatrixInterface::class, $factory->create());
        self::assertInstanceOf(AuthorizationMatrixInterface::class, $factory->create());
        self::assertEquals(1, $stub->getCalls());
    }
}
