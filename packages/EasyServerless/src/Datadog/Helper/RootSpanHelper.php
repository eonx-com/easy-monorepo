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
        $opcacheStatus = self::getOpcacheStatus();
        $opcacheEnabled = self::isOpcacheEnabled(
            $opcacheStatus,
            \extension_loaded('Zend OPcache'),
            \ini_get('opcache.enable'),
            \ini_get('opcache.enable_cli'),
            \PHP_SAPI
        );
        $jitEnabled = $opcacheEnabled
            && self::isJitEnabled($opcacheStatus, \ini_get('opcache.jit'), \ini_get('opcache.jit_buffer_size'));

        $runtimeInfo = [
            'php.max_execution_time' => (string)\ini_get('max_execution_time'),
            'php.memory_limit' => (string)\ini_get('memory_limit'),
            'php.opcache.enabled' => $opcacheEnabled ? 'true' : 'false',
            'php.opcache.jit.enabled' => $jitEnabled ? 'true' : 'false',
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

    /**
     * Uses the runtime status from opcache_get_status() when available, as JIT can be turned off at startup
     * (e.g. by an incompatible extension), and falls back to the ini values otherwise.
     *
     * @param array{jit?: array{on?: bool}}|false $opcacheStatus
     */
    public static function isJitEnabled(
        array|false $opcacheStatus,
        string|false $jit,
        string|false $jitBufferSize,
    ): bool {
        if ($opcacheStatus !== false) {
            return ($opcacheStatus['jit']['on'] ?? false) === true;
        }

        if (\in_array(\strtolower(\trim((string)$jit)), self::JIT_DISABLED_VALUES, true)) {
            return false;
        }

        return (int)$jitBufferSize > 0;
    }

    /**
     * Uses the runtime status from opcache_get_status() when available, as OPcache can fail to start,
     * and falls back to the ini values otherwise.
     *
     * @param array{opcache_enabled?: bool}|false $opcacheStatus
     */
    public static function isOpcacheEnabled(
        array|false $opcacheStatus,
        bool $extensionLoaded,
        string|false $enable,
        string|false $enableCli,
        string $sapi,
    ): bool {
        if ($opcacheStatus !== false) {
            return ($opcacheStatus['opcache_enabled'] ?? false) === true;
        }

        if ($extensionLoaded === false) {
            return false;
        }

        // The opcache.enable ini is the master switch, opcache.enable_cli does not override it when disabled
        if (\filter_var($enable, \FILTER_VALIDATE_BOOL) === false) {
            return false;
        }

        if ($sapi === 'cli') {
            return \filter_var($enableCli, \FILTER_VALIDATE_BOOL);
        }

        return true;
    }

    /**
     * @return array{opcache_enabled?: bool, jit?: array{on?: bool}}|false
     */
    private static function getOpcacheStatus(): array|false
    {
        // The opcache_get_status() function raises a warning outside of the opcache.restrict_api path
        if (\function_exists('opcache_get_status') === false || (string)\ini_get('opcache.restrict_api') !== '') {
            return false;
        }

        return \opcache_get_status(false);
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
}
