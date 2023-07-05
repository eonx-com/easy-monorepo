<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Dotenv\Loaders;

use EonX\EasySsm\Services\Dotenv\Data\EnvData;
use EonX\EasySsm\Services\Dotenv\Loaders\DotenvLoader;
use EonX\EasySsm\Tests\AbstractTestCase;

final class DotenvLoaderTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testLoadEnv
     */
    public static function providerTestLoadEnv(): iterable
    {
        yield '1 env data' => [
            [new EnvData('env', 'value')],
            [
                'env' => 'value',
            ],
        ];

        yield '2 env data' => [
            [new EnvData('env', 'value'), new EnvData('env2', 'value')],
            [
                'env' => 'value',
                'env2' => 'value',
            ],
        ];
    }

    /**
     * @param \EonX\EasySsm\Services\Dotenv\Data\EnvData[] $envs
     * @param mixed[] $expected
     *
     * @dataProvider providerTestLoadEnv
     */
    public function testLoadEnv(array $envs, array $expected): void
    {
        (new DotenvLoader())->loadEnv($envs);

        foreach ($expected as $name => $value) {
            self::assertTrue(isset($_ENV[$name]));
            self::assertTrue(isset($_SERVER[$name]));
            self::assertEquals($value, $_ENV[$name]);
            self::assertEquals($value, $_SERVER[$name]);
            self::assertEquals($value, \getenv($name));
        }
    }
}
