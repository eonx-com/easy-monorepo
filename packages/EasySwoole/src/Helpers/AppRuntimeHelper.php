<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

final class AppRuntimeHelper
{
    private const APP_RUNTIME = 'APP_RUNTIME';

    private const APP_RUNTIME_OPTIONS = 'APP_RUNTIME_OPTIONS';

    public static function setRuntime(string $runtime): void
    {
        $_SERVER[self::APP_RUNTIME] = $runtime;
    }

    /**
     * @param mixed[] $options
     */
    public static function addOptions(array $options): void
    {
        $_SERVER[self::APP_RUNTIME_OPTIONS] = \array_merge(
            $_SERVER[self::APP_RUNTIME_OPTIONS] ?? [],
            $options
        );
    }
}
