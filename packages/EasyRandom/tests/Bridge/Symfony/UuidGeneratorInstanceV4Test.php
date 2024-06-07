<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Symfony;

use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use EonX\EasyRandom\Tests\Bridge\Symfony\Fixtures\App\Kernel\ApplicationKernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Uid\UuidV4;

final class UuidGeneratorInstanceV4Test extends AbstractSymfonyTestCase
{
    public function testUuidGeneratorInstance(): void
    {
        $result = self::getService(UuidGeneratorInterface::class);

        $uuidFactory = self::getPrivatePropertyValue($result, 'uuidFactory');
        self::assertSame(UuidV4::class, self::getPrivatePropertyValue($uuidFactory, 'defaultClass'));
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new ApplicationKernel('test_v4', false);
    }
}
