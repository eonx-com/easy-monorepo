<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Symfony;

use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use EonX\EasyRandom\Tests\Bridge\Symfony\Fixtures\App\Kernel\ApplicationKernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Uid\UuidV6;

final class UuidGeneratorInstanceV6Test extends AbstractSymfonyTestCase
{
    public function testUuidGeneratorInstance(): void
    {
        $result = self::getService(UuidGeneratorInterface::class);

        $uuidFactory = self::getPrivatePropertyValue($result, 'uuidFactory');
        self::assertSame(UuidV6::class, self::getPrivatePropertyValue($uuidFactory, 'defaultClass'));
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new ApplicationKernel('test_v6', false);
    }
}
