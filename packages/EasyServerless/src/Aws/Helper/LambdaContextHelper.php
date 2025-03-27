<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Aws\Helper;

final class LambdaContextHelper
{
    public static function getInvocationContext(): array
    {
        return (array)\json_decode($_SERVER['LAMBDA_INVOCATION_CONTEXT'] ?? '[]', true);
    }

    public static function getRequestContext(): array
    {
        return (array)\json_decode($_SERVER['LAMBDA_REQUEST_CONTEXT'] ?? '[]', true);
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
