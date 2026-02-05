<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Helper;

final class LambdaContextHelper
{
    public static function getAbsolutePath(string $path): string
    {
        return \sprintf('%s/%s', \getenv('LAMBDA_TASK_ROOT'), $path);
    }

    public static function getInvocationContext(): array
    {
        /** @var string $lambdaInvocationContext */
        $lambdaInvocationContext = $_SERVER['LAMBDA_INVOCATION_CONTEXT'] ?? '[]';

        return (array)\json_decode($lambdaInvocationContext, true);
    }

    public static function getRemainingTimeInMilliseconds(): int
    {
        return (self::getInvocationContext()['deadlineMs'] ?? 0) - (int)(\microtime(true) * 1000);
    }

    public static function getRequestContext(): array
    {
        /** @var string $lambdaRequestContext */
        $lambdaRequestContext = $_SERVER['LAMBDA_REQUEST_CONTEXT'] ?? '[]';

        return (array)\json_decode($lambdaRequestContext, true);
    }

    public static function inLambda(): bool
    {
        return \getenv('LAMBDA_TASK_ROOT') !== false;
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
