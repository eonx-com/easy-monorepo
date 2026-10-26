<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Datadog\Helper;

use EonX\EasyServerless\Datadog\Helper\RootSpanHelper;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;

final class RootSpanHelperTest extends AbstractUnitTestCase
{
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
}
