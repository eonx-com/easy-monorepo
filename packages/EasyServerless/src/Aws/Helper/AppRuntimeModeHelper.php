<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Helper;

final class AppRuntimeModeHelper
{
    private const APP_RUNTIME_MODE = 'APP_RUNTIME_MODE';

    public static function ensureHttpRuntimeMode(): void
    {
        self::setIfMissing('web=1&worker=1');
    }

    public static function ensureWorkerRuntimeMode(): void
    {
        self::setIfMissing('worker=1');
    }

    private static function setIfMissing(string $value): void
    {
        $current = \getenv(self::APP_RUNTIME_MODE);

        if (\is_string($current) && $current !== '') {
            return;
        }

        foreach ([$_ENV, $_SERVER] as $scope) {
            $current = $scope[self::APP_RUNTIME_MODE] ?? null;

            if (\is_string($current) && $current !== '') {
                return;
            }
        }

        $_ENV[self::APP_RUNTIME_MODE] = $value;
        $_SERVER[self::APP_RUNTIME_MODE] = $value;
        \putenv(\sprintf('%s=%s', self::APP_RUNTIME_MODE, $value));
    }
}
