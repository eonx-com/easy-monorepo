<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Helpers;

use EonX\EasySwoole\Helpers\EnvVarHelper;
use EonX\EasySwoole\Helpers\OptionHelper;
use EonX\EasySwoole\Tests\AbstractTestCase;

final class EnvVarHelperTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestLoadEnvVars(): iterable
    {
        yield 'simple test' => [
            [
                'SECRET_TEST' => '{"TEST":"test"}',
            ],
            [
                'TEST' => 'test',
            ],
        ];

        yield 'simple test all lowercase' => [
            [
                'secret_test' => '{"TEST_1":"test"}',
            ],
            [
                'TEST_1' => 'test',
            ],
        ];

        yield 'simple test some lowercase' => [
            [
                'secret_TEST' => '{"TEST_2":"test"}',
            ],
            [
                'TEST_2' => 'test',
            ],
        ];

        yield 'custom jsonSecret matching only 1 env var' => [
            [
                'SECRET_TEST' => '{"TEST":"test"}',
                'NOT_SECRET_TEST' => '{"TEST_1":"test", "TEST_2":"test"}',
            ],
            [
                'TEST_1' => 'test',
                'TEST_2' => 'test',
            ],
            [
                'NOT_SECRET_.+',
            ],
        ];
    }

    /**
     * @param mixed[] $secrets
     * @param mixed[] $expected
     * @param null|string[] $jsonSecrets
     *
     * @dataProvider providerTestLoadEnvVars
     */
    public function testLoadEnvVars(array $secrets, array $expected, ?array $jsonSecrets = null): void
    {
        foreach ($secrets as $name => $value) {
            $_SERVER[$name] = $value;
        }

        if ($jsonSecrets !== null) {
            OptionHelper::setOption('json_secrets', $jsonSecrets);
        }

        EnvVarHelper::disableOutput();
        EnvVarHelper::loadEnvVars(OptionHelper::getArray('json_secrets'));

        foreach ($expected as $key => $value) {
            self::assertEquals($value, $_ENV[$key]);
            self::assertEquals($value, $_SERVER[$key]);

            unset($_ENV[$key], $_SERVER[$key]);
        }
    }
}
