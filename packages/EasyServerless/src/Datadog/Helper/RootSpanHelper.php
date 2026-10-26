<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Datadog\Helper;

use DDTrace\SpanData;

use function DDTrace\root_span;

final class RootSpanHelper
{
    private const JIT_DISABLED_VALUES = ['', '0', 'disable', 'disabled', 'false', 'no', 'off'];

    /**
     * Adds PHP runtime information as tags on the root span of the current trace.
     * No-op when the ddtrace extension is not loaded or when no trace is active.
     */
    public static function addPhpRuntimeInfo(): void
    {
        $rootSpan = self::getRootSpan();

        if ($rootSpan === null) {
            return;
        }

        foreach (self::getPhpRuntimeInfo() as $key => $value) {
            $rootSpan->meta[$key] = $value;
        }

        foreach (self::getPhpRuntimeMetrics() as $key => $value) {
            $rootSpan->metrics[$key] = $value;
        }
    }

    /**
     * @return array<string, string>
     */
    public static function getPhpRuntimeInfo(): array
    {
        $opcacheEnabled = self::isOpcacheEnabled();

        $runtimeInfo = [
            'php.max_execution_time' => (string)\ini_get('max_execution_time'),
            'php.memory_limit' => (string)\ini_get('memory_limit'),
            'php.opcache.enabled' => $opcacheEnabled ? 'true' : 'false',
            'php.opcache.jit.enabled' => $opcacheEnabled && self::isJitEnabled() ? 'true' : 'false',
            'php.os' => \PHP_OS_FAMILY,
            'php.sapi' => \PHP_SAPI,
            'php.version' => \PHP_VERSION,
        ];

        if (\PHP_EXTRA_VERSION !== '') {
            $runtimeInfo['php.extra_version'] = \PHP_EXTRA_VERSION;
        }

        return $runtimeInfo;
    }

    /**
     * @return array<string, int>
     */
    public static function getPhpRuntimeMetrics(): array
    {
        return [
            'php.version_id' => \PHP_VERSION_ID,
        ];
    }

    private static function getRootSpan(): ?SpanData
    {
        // The function only exists when the ddtrace extension is loaded,
        // and returns null when tracing is disabled or no trace is active (e.g. CLI without DD_TRACE_CLI_ENABLED)
        if (\function_exists('DDTrace\root_span') === false) {
            return null;
        }

        return root_span();
    }

    private static function isJitEnabled(): bool
    {
        $jit = \strtolower(\trim((string)\ini_get('opcache.jit')));

        if (\in_array($jit, self::JIT_DISABLED_VALUES, true)) {
            return false;
        }

        return (int)\ini_get('opcache.jit_buffer_size') > 0;
    }

    private static function isOpcacheEnabled(): bool
    {
        if (\extension_loaded('Zend OPcache') === false) {
            return false;
        }

        $ini = \PHP_SAPI === 'cli' ? 'opcache.enable_cli' : 'opcache.enable';

        return \filter_var(\ini_get($ini), \FILTER_VALIDATE_BOOL);
    }
}
