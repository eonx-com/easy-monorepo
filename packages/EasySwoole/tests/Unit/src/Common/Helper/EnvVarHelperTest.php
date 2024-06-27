<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Unit\Common\Helper;

use EonX\EasySwoole\Common\Helper\EnvVarHelper;
use EonX\EasySwoole\Common\Helper\OptionHelper;
use EonX\EasySwoole\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class EnvVarHelperTest extends AbstractUnitTestCase
{
    /**
     * @see testLoadEnvVars
     */
    public static function providerTestLoadEnvVars(): iterable
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
     * @param string[]|null $jsonSecrets
     */
    #[DataProvider('providerTestLoadEnvVars')]
    public function testLoadEnvVars(array $secrets, array $expected, ?array $jsonSecrets = null): void
    {
        foreach ($secrets as $name => $value) {
            $_SERVER[$name] = $value;
        }

        if ($jsonSecrets !== null) {
            OptionHelper::setOption('json_secrets', $jsonSecrets);
        }

        EnvVarHelper::loadEnvVars(
            OptionHelper::getArray('json_secrets'),
            outputEnabled: false,
        );

        foreach ($expected as $key => $value) {
            self::assertEquals($value, $_ENV[$key]);
            self::assertEquals($value, $_SERVER[$key]);

            unset($_ENV[$key], $_SERVER[$key]);
        }
    }
}
