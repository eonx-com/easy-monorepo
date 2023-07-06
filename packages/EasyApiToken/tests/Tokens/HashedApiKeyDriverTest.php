<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Tokens;

use EonX\EasyApiToken\Interfaces\Tokens\HashedApiKeyInterface;
use EonX\EasyApiToken\Tests\AbstractTestCase;
use EonX\EasyApiToken\Tokens\HashedApiKeyDriver;

final class HashedApiKeyDriverTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testDriver
     */
    public static function providerTestDriver(): iterable
    {
        yield 'Int id' => [1, 'secret'];

        yield 'String id' => ['id', 'secret'];

        yield 'Custom version' => ['id', 'secret', 'my-version'];
    }

    /**
     * @dataProvider providerTestDriver
     */
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
