<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\Authorization\Factory;

use EonX\EasySecurity\Authorization\Factory\CachedAuthorizationMatrixFactory;
use EonX\EasySecurity\Authorization\Provider\AuthorizationMatrixProviderInterface;
use EonX\EasySecurity\Tests\Stub\Factory\AuthorizationMatrixFactoryStub;
use EonX\EasySecurity\Tests\Unit\AbstractUnitTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class CachedAuthorizationMatrixFactoryTest extends AbstractUnitTestCase
{
    public function testCacheWorking(): void
    {
        $cache = new ArrayAdapter();
        $stub = new AuthorizationMatrixFactoryStub();
        $factory = new CachedAuthorizationMatrixFactory($cache, $stub);

        self::assertInstanceOf(AuthorizationMatrixProviderInterface::class, $factory->create());
        self::assertInstanceOf(AuthorizationMatrixProviderInterface::class, $factory->create());
        self::assertEquals(1, $stub->getCalls());
    }
}
