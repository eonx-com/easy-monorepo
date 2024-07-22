<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Unit\Common\Driver;

use EonX\EasyApiToken\Common\Driver\HashedApiKeyDriver;
use EonX\EasyApiToken\Common\ValueObject\HashedApiKeyInterface;
use EonX\EasyApiToken\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class HashedApiKeyDriverTest extends AbstractUnitTestCase
{
    /**
     * @see testDriver
     */
    public static function provideDriverData(): iterable
    {
        yield 'Int id' => [1, 'secret'];

        yield 'String id' => ['id', 'secret'];

        yield 'Custom version' => ['id', 'secret', 'my-version'];
    }

    #[DataProvider('provideDriverData')]
    public function testDriver(int|string $id, string $secret, ?string $version = null): void
    {
        $driver = new HashedApiKeyDriver();
        $token = $driver->encode($id, $secret, $version);
        $hashedApiKey = $driver->decode($token);

        $expectedPayload = [
            'id' => $id,
            'secret' => $secret,
            'version' => $version ?? HashedApiKeyInterface::DEFAULT_VERSION,
        ];

        self::assertInstanceOf(HashedApiKeyInterface::class, $hashedApiKey);
        if ($hashedApiKey instanceof HashedApiKeyInterface) {
            self::assertEquals($expectedPayload['id'], $hashedApiKey->getId());
            self::assertEquals($expectedPayload['secret'], $hashedApiKey->getSecret());
            self::assertEquals($expectedPayload['version'], $hashedApiKey->getVersion());
            self::assertEquals($token, $hashedApiKey->getOriginalToken());
            self::assertEquals($expectedPayload, $hashedApiKey->getPayload());
        }
    }
}
