<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use EonX\EasySwoole\Bridge\EasySchedule\EasyScheduleSwooleRunner;
use EonX\EasySwoole\Runtime\EasySwooleRuntime;

final class AppRuntimeHelper
{
    public const EVENT_AFTER_RELOAD = 'beforeReload';

    public const EVENT_BEFORE_RELOAD = 'beforeReload';

    public const EVENT_CLOSE = 'close';

    public const EVENT_CONNECT = 'connect';

    public const EVENT_ENV_VARS_LOADED = 'envVarsLoaded';

    public const EVENT_FINISH = 'finish';

    public const EVENT_MANAGER_START = 'managerStart';

    public const EVENT_MANAGER_STOP = 'managerStop';

    public const EVENT_PIPE_MESSAGE = 'pipeMessage';

    public const EVENT_RECEIVE = 'receive';

    public const EVENT_REQUEST = 'request';

    public const EVENT_SHUTDOWN = 'shutdown';

    public const EVENT_TASK = 'task';

    public const EVENT_WORKER_ERROR = 'workerError';

    public const EVENT_WORKER_EXIT = 'workerExit';

    public const EVENT_WORKER_START = 'workerStart';

    public const EVENT_WORKER_STOP = 'workerStop';

    private const APP_RUNTIME = 'APP_RUNTIME';

    private const APP_RUNTIME_OPTIONS = 'APP_RUNTIME_OPTIONS';

    public static function addOptions(array $options): void
    {
        $_SERVER[self::APP_RUNTIME_OPTIONS] = \array_merge(
            $_SERVER[self::APP_RUNTIME_OPTIONS] ?? [],
            $options
        );
    }

    public static function enableAppCacheWarmup(): void
    {
        self::addOptions(['app_cache_warmup_enabled' => true]);
    }

    public static function enableEasyScheduleRunner(): void
    {
        self::addOptions([EasyScheduleSwooleRunner::ENABLED => true]);
    }

    public static function enableRuntime(): void
    {
        self::setRuntime(EasySwooleRuntime::class);
    }

    public static function getOption(string $name, mixed $default = null): mixed
    {
        return $_SERVER[self::APP_RUNTIME_OPTIONS][$name] ?? $default;
    }

    public static function onEnvVarsLoaded(callable $callback): void
    {
        $callbacks = self::getOption('callbacks', []);
        $callbacks[self::EVENT_ENV_VARS_LOADED] = $callback;

        self::addOptions(['callbacks' => $callbacks]);
    }

    public static function setCacheClearAfterTickCount(int $cacheClearAfterTickCount): void
    {
        self::addOptions(['cache_clear_after_tick_count' => $cacheClearAfterTickCount]);
    }

    public static function setCacheTables(array $cacheTables): void
    {
        self::addOptions(['cache_tables' => $cacheTables]);
    }

    /**
     * @param callable[] $callbacks
     */
    public static function setCallbacks(array $callbacks): void
    {
        self::addOptions(['callbacks' => $callbacks]);
    }

    public static function setEnvVarName(string $envVarName): void
    {
        self::addOptions(['env_var_name' => $envVarName]);
    }

    public static function setHost(string $host): void
    {
        self::addOptions(['host' => $host]);
    }

    /**
     * @param string[] $hotReloadDirs
     */
    public static function setHotReloadDirs(array $hotReloadDirs): void
    {
        self::addOptions(['hot_reload_dirs' => $hotReloadDirs]);
    }

    public static function setHotReloadEnabled(bool $hotReloadEnabled): void
    {
        self::addOptions(['hot_reload_enabled' => $hotReloadEnabled]);
    }

    /**
     * @param string[] $hotReloadExtensions
     */
    public static function setHotReloadExtensions(array $hotReloadExtensions): void
    {
        self::addOptions(['hot_reload_extensions' => $hotReloadExtensions]);
    }

    /**
     * @param string[] $jsonSecrets
     */
    public static function setJsonSecrets(array $jsonSecrets): void
    {
        self::addOptions(['json_secrets' => $jsonSecrets]);
    }

    public static function setMode(int $mode): void
    {
        self::addOptions(['mode' => $mode]);
    }

    public static function setPort(int $port): void
    {
        self::addOptions(['port' => $port]);
    }

    public static function setResponseChunkSize(int $responseChunkSize): void
    {
        self::addOptions(['response_chunk_size' => $responseChunkSize]);
    }

    public static function setRuntime(string $runtime): void
    {
        $_SERVER[self::APP_RUNTIME] = $runtime;
    }

    public static function setSettings(array $settings): void
    {
        self::addOptions(['settings' => $settings]);
    }

    public static function setSockType(int $sockType): void
    {
        self::addOptions(['sock_type' => $sockType]);
    }

    public static function setSslCertEnvVarName(string $sslCertEnvVarName): void
    {
        self::addOptions(['ssl_cert_env_var_name' => $sslCertEnvVarName]);
    }

    public static function setSslKeyEnvVarName(string $sslKeyEnvVarName): void
    {
        self::addOptions(['ssl_key_env_var_name' => $sslKeyEnvVarName]);
    }

    public static function setUseDefaultCallbacks(bool $useDefaultCallbacks): void
    {
        self::addOptions(['use_default_callbacks' => $useDefaultCallbacks]);
    }

    public static function setWorkerStopWaitEvent(bool $workerStopWaitEvent): void
    {
        self::addOptions(['worker_stop_wait_event' => $workerStopWaitEvent]);
    }
}
