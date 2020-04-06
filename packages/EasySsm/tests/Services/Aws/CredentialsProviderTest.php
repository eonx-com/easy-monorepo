<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Aws;

use EonX\EasySsm\Services\Aws\CredentialsProvider;
use EonX\EasySsm\Tests\AbstractTestCase;

final class CredentialsProviderTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestGetCredentials(): iterable
    {
        yield 'defaults' => [
            [
                'version' => 'latest',
                'region' => 'ap-southeast-2',
                'profile' => 'default',
            ],
        ];

        yield 'with credentials in constructor' => [
            [
                'version' => 'latest',
                'region' => 'ap-southeast-2',
                'credentials' => ['key' => 'key', 'secret' => 'secret'],
            ],
            ['key' => 'key', 'secret' => 'secret'],
        ];

        yield 'with credentials in env' => [
            [
                'version' => 'latest',
                'region' => 'ap-southeast-2',
                'credentials' => ['key' => 'key', 'secret' => 'secret'],
            ],
            null,
            ['AWS_KEY' => 'key', 'AWS_SECRET' => 'secret'],
        ];
    }

    /**
     * @param mixed[] $expected
     * @param null|mixed[] $constructor
     * @param null|mixed[] $envs
     *
     * @dataProvider providerTestGetCredentials
     */
    public function testGetCredentials(array $expected, ?array $constructor = null, ?array $envs = null): void
    {
        foreach ($envs ?? [] as $name => $value) {
            \putenv(\sprintf('%s=%s', $name, $value));
        }

        self::assertEquals($expected, (new CredentialsProvider($constructor))->getCredentials());
    }
}
