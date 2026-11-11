<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Datadog\Helper;

use EonX\EasyServerless\Datadog\Helper\RootSpanHelper;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

use function DDTrace\root_span;

final class RootSpanHelperTest extends AbstractUnitTestCase
{
    /**
     * @see testIsJitEnabled
     */
    public static function provideJitEnabledData(): iterable
    {
        yield 'status, on' => [['jit' => ['on' => true]], 'disable', '0', true];
        yield 'status, off even if ini enables it' => [['jit' => ['on' => false]], 'tracing', '64M', false];
        yield 'status without jit' => [[], 'tracing', '64M', false];
        yield 'no status, tracing with buffer' => [false, 'tracing', '64M', true];
        yield 'no status, numeric with buffer' => [false, '1255', '64M', true];
        yield 'no status, tracing without buffer' => [false, 'tracing', '0', false];
        yield 'no status, disable' => [false, 'disable', '64M', false];
        yield 'no status, off (empty ini value)' => [false, '', '64M', false];
        yield 'no status, 0' => [false, '0', '64M', false];
        yield 'no status, ini missing' => [false, false, false, false];
    }

    /**
     * @see testIsOpcacheEnabled
     */
    public static function provideOpcacheEnabledData(): iterable
    {
        yield 'status, enabled' => [['opcache_enabled' => true], false, '0', '0', 'cli', true];
        yield 'status, disabled' => [['opcache_enabled' => false], true, '1', '1', 'fpm-fcgi', false];
        yield 'no status, extension not loaded' => [false, false, '1', '1', 'fpm-fcgi', false];
        yield 'no status, fpm, enabled' => [false, true, '1', '0', 'fpm-fcgi', true];
        yield 'no status, fpm, disabled' => [false, true, '0', '1', 'fpm-fcgi', false];
        yield 'no status, cli, enable_cli on' => [false, true, '1', '1', 'cli', true];
        yield 'no status, cli, enable_cli off' => [false, true, '1', '0', 'cli', false];
        yield 'no status, cli, enable off overrides enable_cli' => [false, true, '0', '1', 'cli', false];
    }

    #[RunInSeparateProcess]
    public function testAddPhpRuntimeInfoAddsTagsAndMetricsToRootSpan(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        require_once __DIR__ . '/../../../../Fixture/ddtrace/ddtrace.php';
        $rootSpan = root_span();
        self::assertNotNull($rootSpan);
        $rootSpan->meta['existing.tag'] = 'value';
        $rootSpan->metrics['existing.metric'] = 1;

        RootSpanHelper::addPhpRuntimeInfo();

        self::assertSame(['existing.tag' => 'value', ...RootSpanHelper::getPhpRuntimeInfo()], $rootSpan->meta);
        self::assertSame(['existing.metric' => 1, 'php.version_id' => \PHP_VERSION_ID], $rootSpan->metrics);
    }

    public function testAddPhpRuntimeInfoIsNoOpWithoutDdTrace(): void
    {
        if (\extension_loaded('ddtrace')) {
            self::markTestSkipped('The ddtrace extension is loaded');
        }

        RootSpanHelper::addPhpRuntimeInfo();

        $this->addToAssertionCount(1);
    }

    public function testGetPhpRuntimeInfoSucceeds(): void
    {
        $runtimeInfo = RootSpanHelper::getPhpRuntimeInfo();

        self::assertSame(\PHP_VERSION, $runtimeInfo['php.version']);
        self::assertSame(\PHP_SAPI, $runtimeInfo['php.sapi']);
        self::assertSame(\PHP_OS_FAMILY, $runtimeInfo['php.os']);
        self::assertSame((string)\ini_get('memory_limit'), $runtimeInfo['php.memory_limit']);
        self::assertSame((string)\ini_get('max_execution_time'), $runtimeInfo['php.max_execution_time']);
        self::assertContains($runtimeInfo['php.opcache.enabled'], ['true', 'false']);
        self::assertContains($runtimeInfo['php.opcache.jit.enabled'], ['true', 'false']);

        if ($runtimeInfo['php.opcache.enabled'] === 'false') {
            self::assertSame('false', $runtimeInfo['php.opcache.jit.enabled']);
        }

        if (\PHP_EXTRA_VERSION !== '') {
            self::assertSame(\PHP_EXTRA_VERSION, $runtimeInfo['php.extra_version']);
        }

        if (\PHP_EXTRA_VERSION === '') {
            self::assertArrayNotHasKey('php.extra_version', $runtimeInfo);
        }

        foreach ($runtimeInfo as $key => $value) {
            self::assertStringStartsWith('php.', $key);
            self::assertIsString($value);
        }
    }

    public function testGetPhpRuntimeMetricsSucceeds(): void
    {
        $runtimeMetrics = RootSpanHelper::getPhpRuntimeMetrics();

        self::assertSame(['php.version_id' => \PHP_VERSION_ID], $runtimeMetrics);
    }

    /**
     * @param array{jit?: array{on?: bool}}|false $opcacheStatus
     */
    #[DataProvider('provideJitEnabledData')]
    public function testIsJitEnabled(
        array|false $opcacheStatus,
        string|false $jit,
        string|false $jitBufferSize,
        bool $expected,
    ): void {
        $result = RootSpanHelper::isJitEnabled($opcacheStatus, $jit, $jitBufferSize);

        self::assertSame($expected, $result);
    }

    /**
     * @param array{opcache_enabled?: bool}|false $opcacheStatus
     */
    #[DataProvider('provideOpcacheEnabledData')]
    public function testIsOpcacheEnabled(
        array|false $opcacheStatus,
        bool $extensionLoaded,
        string|false $enable,
        string|false $enableCli,
        string $sapi,
        bool $expected,
    ): void {
        $result = RootSpanHelper::isOpcacheEnabled($opcacheStatus, $extensionLoaded, $enable, $enableCli, $sapi);

        self::assertSame($expected, $result);
    }
}
