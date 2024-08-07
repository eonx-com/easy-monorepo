<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Helper;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;
use EonX\EasyUtils\Common\Helper\ErrorDetailsHelper;
use Swoole\Http\Response;
use Throwable;

final class ErrorResponseHelper
{
    public static function sendErrorResponse(Throwable $throwable, Response $response): void
    {
        $response->status(
            HttpStatusCode::InternalServerError->value,
            HttpStatusCode::InternalServerError->description()
        );

        $response->header('Content-Type', 'application/json');
        $response->end(self::getContent($throwable));
    }

    private static function getContent(Throwable $throwable): string
    {
        $details = ['error' => $throwable->getMessage()];

        if (\class_exists(ErrorDetailsHelper::class)) {
            $details = \array_merge(ErrorDetailsHelper::resolveSimpleDetails($throwable), $details);
        }

        return (string)\json_encode($details);
    }
}
