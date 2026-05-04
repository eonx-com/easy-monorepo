<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Helper;

final class LambdaContextHelper
{
    public static function getAbsolutePath(string $path): string
    {
        return \sprintf(
            '%s%s%s',
            self::getTaskRoot(),
            self::inLambda() ? '/' : '',
            $path
        );
    }

    public static function getHandler(): ?string
    {
        $handler = \getenv('_HANDLER');

        return \is_string($handler) && $handler !== '' ? $handler : null;
    }

    public static function getInvocationContext(): array
    {
        return (array)\json_decode($_SERVER['LAMBDA_INVOCATION_CONTEXT'] ?? '[]', true);
    }

    public static function getRemainingTimeInMilliseconds(): int
    {
        return (self::getInvocationContext()['deadlineMs'] ?? 0) - (int)(\microtime(true) * 1000);
    }

    public static function getRequestContext(): array
    {
        return (array)\json_decode($_SERVER['LAMBDA_REQUEST_CONTEXT'] ?? '[]', true);
    }

    public static function getTaskRoot(): ?string
    {
        $taskRoot = \getenv('LAMBDA_TASK_ROOT');

        return \is_string($taskRoot) && $taskRoot !== '' ? $taskRoot : null;
    }

    public static function inLambda(): bool
    {
        return self::getTaskRoot() !== null;
    }

    public static function inLocalLambda(): bool
    {
        return isset($_SERVER['AWS_SAM_LOCAL']);
    }

    public static function inRemoteLambda(): bool
    {
        return self::inLambda() === true && self::inLocalLambda() === false;
    }
}
