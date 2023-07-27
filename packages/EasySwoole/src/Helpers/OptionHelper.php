<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use function Symfony\Component\String\u;

final class OptionHelper
{
    private const DEFAULT_CACHE_CLEAR_AFTER_TICK_COUNT = 10000;

    private const DEFAULT_OPTIONS = [
        'cache_clear_after_tick_count' => self::DEFAULT_CACHE_CLEAR_AFTER_TICK_COUNT,
        'cache_tables' => [],
        'callbacks' => [],
        'env_var_output_enabled' => true,
        'host' => '0.0.0.0',
        'hot_reload_dirs' => [
            self::DEFAULT_PUBLIC_DIR . 'config',
            self::DEFAULT_PUBLIC_DIR . 'public',
            self::DEFAULT_PUBLIC_DIR . 'src',
            self::DEFAULT_PUBLIC_DIR . 'translations',
            self::DEFAULT_PUBLIC_DIR . 'vendor',
        ],
        'hot_reload_enabled' => false,
        'hot_reload_extensions' => [
            '.php',
            '.yaml',
            '.xml',
        ],
        'json_secrets' => ['SECRET_.+'],
        'mode' => 1,
        'port' => 8080,
        'response_chunk_size' => 1_048_576,
        'settings' => [],
        'sock_type' => 1,
        'use_default_callbacks' => true,
    ];

    private const DEFAULT_PUBLIC_DIR = __DIR__ . '/../../../../../';

    private static array $options = [];

    public static function getArray(string $option, ?string $env = null): array
    {
        $value = self::getOption($option, $env);

        if (\is_string($value)) {
            return \explode(',', $value);
        }

        return \array_filter(\is_array($value) ? $value : [$value]);
    }

    public static function getBoolean(string $option, ?string $env = null): bool
    {
        $value = self::getOption($option, $env);

        if (\is_numeric($value)) {
            return (float)$value > 0;
        }

        if (\is_string($value)) {
            return $value === 'true';
        }

        return (bool)$value;
    }

    public static function getInteger(string $option, ?string $env = null): int
    {
        return (int)self::getOption($option, $env);
    }

    public static function getOptions(): array
    {
        return self::$options;
    }

    public static function getString(string $option, ?string $env = null): string
    {
        return (string)self::getOption($option, $env);
    }

    public static function getStringNullable(string $option, ?string $env = null): ?string
    {
        $value = self::getOption($option, $env);

        return \is_string($value) && $value !== '' ? $value : null;
    }

    public static function isset(string $option, ?string $env = null): bool
    {
        return self::getOption($option, $env, false) !== null;
    }

    public static function setOption(string $name, mixed $value): void
    {
        self::$options[$name] = $value;
    }

    public static function setOptions(array $options): void
    {
        self::$options = $options;
    }

    private static function getOption(string $option, ?string $env = null, ?bool $useDefault = null): mixed
    {
        $env ??= \sprintf('SWOOLE_%s', u($option)->upper());
        $value = self::$options[$option] ?? $_SERVER[$env] ?? $_ENV[$env] ?? null;

        if (($useDefault ?? true) && $value === null) {
            $value = self::DEFAULT_OPTIONS[$option] ?? $value;
        }

        return $value;
    }
}
