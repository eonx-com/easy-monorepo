<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use EonX\EasyUtils\Helpers\ErrorDetailsHelper;
use Swoole\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Throwable;

final class ErrorResponseHelper
{
    public static function sendErrorResponse(Throwable $throwable, Response $response): void
    {
        $response->status(
            HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR,
            HttpFoundationResponse::$statusTexts[HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR]
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
