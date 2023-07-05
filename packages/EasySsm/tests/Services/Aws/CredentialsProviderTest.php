<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Aws;

use EonX\EasyAwsCredentialsFinder\AwsCredentialsProvider;
use EonX\EasyAwsCredentialsFinder\Finders\EnvsCredentialsFinder;
use EonX\EasySsm\Services\Aws\CredentialsProvider;
use EonX\EasySsm\Tests\AbstractTestCase;

final class CredentialsProviderTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testGetCredentials
     */
    public static function providerTestGetCredentials(): iterable
    {
        yield 'defaults' => [
            [
                'version' => 'latest',
                'region' => 'ap-southeast-2',
            ],
        ];

        yield 'with credentials in env' => [
            [
                'version' => 'latest',
                'region' => 'ap-southeast-2',
                'credentials' => [
                    'key' => 'key',
                    'secret' => 'secret',
                ],
            ],
            [
                'AWS_KEY' => 'key',
                'AWS_SECRET' => 'secret',
            ],
        ];
    }

    /**
     * @param mixed[] $expected
     * @param null|mixed[] $envs
     *
     * @dataProvider providerTestGetCredentials
     */
    public function testGetCredentials(array $expected, ?array $envs = null): void
    {
        foreach ($envs ?? [] as $name => $value) {
            \putenv(\sprintf('%s=%s', $name, $value));
        }

        $awsCredentialsProvider = new AwsCredentialsProvider([new EnvsCredentialsFinder()]);
        $credentialsProvider = new CredentialsProvider($awsCredentialsProvider);

        self::assertEquals($expected, $credentialsProvider->getCredentials());
    }
}
