<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\src\Aws\Helper;

use EonX\EasyServerless\Aws\Helper\LambdaContextHelper;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

final class LambdaContextHelperTest extends AbstractUnitTestCase
{
    /**
     * @see testInSnapStartLambda
     */
    public static function provideInSnapStartLambdaData(): iterable
    {
        yield 'remote Lambda with SnapStart' => [
            'env' => [
                'AWS_LAMBDA_INITIALIZATION_TYPE' => 'snap-start',
                'LAMBDA_TASK_ROOT' => '/var/task',
            ],
            'server' => [],
            'expected' => true,
        ];
        yield 'remote Lambda on demand' => [
            'env' => [
                'AWS_LAMBDA_INITIALIZATION_TYPE' => 'on-demand',
                'LAMBDA_TASK_ROOT' => '/var/task',
            ],
            'server' => [],
            'expected' => false,
        ];
        yield 'remote Lambda with provisioned concurrency' => [
            'env' => [
                'AWS_LAMBDA_INITIALIZATION_TYPE' => 'provisioned-concurrency',
                'LAMBDA_TASK_ROOT' => '/var/task',
            ],
            'server' => [],
            'expected' => false,
        ];
        yield 'remote Lambda without initialization type' => [
            'env' => [
                'AWS_LAMBDA_INITIALIZATION_TYPE' => null,
                'LAMBDA_TASK_ROOT' => '/var/task',
            ],
            'server' => [],
            'expected' => false,
        ];
        yield 'local Lambda with SnapStart' => [
            'env' => [
                'AWS_LAMBDA_INITIALIZATION_TYPE' => 'snap-start',
                'LAMBDA_TASK_ROOT' => '/var/task',
            ],
            'server' => [
                'AWS_SAM_LOCAL' => 'true',
            ],
            'expected' => false,
        ];
        yield 'outside Lambda' => [
            'env' => [
                'AWS_LAMBDA_INITIALIZATION_TYPE' => 'snap-start',
                'LAMBDA_TASK_ROOT' => null,
            ],
            'server' => [],
            'expected' => false,
        ];
    }

    public function testGetInvocationContextSucceeds(): void
    {
        self::assertIsArray(LambdaContextHelper::getInvocationContext());
    }

    public function testGetRequestContextSucceeds(): void
    {
        self::assertIsArray(LambdaContextHelper::getRequestContext());
    }

    /**
     * @param array<string, string|null> $env The null values unset the environment variables
     * @param array<string, string> $server
     */
    #[DataProvider('provideInSnapStartLambdaData')]
    #[RunInSeparateProcess]
    public function testInSnapStartLambda(array $env, array $server, bool $expected): void
    {
        foreach ($env as $name => $value) {
            \putenv($value === null ? $name : $name . '=' . $value);
        }
        foreach ($server as $name => $value) {
            $_SERVER[$name] = $value;
        }

        $result = LambdaContextHelper::inSnapStartLambda();

        self::assertSame($expected, $result);
    }
}
