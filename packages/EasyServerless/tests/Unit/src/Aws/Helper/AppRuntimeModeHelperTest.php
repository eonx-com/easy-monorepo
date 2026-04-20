<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\src\Aws\Helper;

use EonX\EasyServerless\Aws\Helper\AppRuntimeModeHelper;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AppRuntimeModeHelperTest extends AbstractUnitTestCase
{
    protected function tearDown(): void
    {
        \putenv('APP_RUNTIME_MODE');
        unset($_ENV['APP_RUNTIME_MODE'], $_SERVER['APP_RUNTIME_MODE']);

        parent::tearDown();
    }

    /**
     * @see testEnsureRuntimeModeDoesNotOverrideExplicitSuperglobalConfiguration
     */
    public static function provideEnsureRuntimeModeMethods(): iterable
    {
        yield 'HTTP runtime mode' => [
            'method' => 'ensureHttpRuntimeMode',
        ];

        yield 'Worker runtime mode' => [
            'method' => 'ensureWorkerRuntimeMode',
        ];
    }

    #[DataProvider('provideEnsureRuntimeModeMethods')]
    public function testEnsureRuntimeModeDoesNotOverrideExplicitSuperglobalConfiguration(string $method): void
    {
        $_ENV['APP_RUNTIME_MODE'] = $_SERVER['APP_RUNTIME_MODE'] = 'custom=1';

        AppRuntimeModeHelper::{$method}();

        self::assertFalse(\getenv('APP_RUNTIME_MODE'));
        self::assertSame('custom=1', $_ENV['APP_RUNTIME_MODE']);
        self::assertSame('custom=1', $_SERVER['APP_RUNTIME_MODE']);
    }
}
