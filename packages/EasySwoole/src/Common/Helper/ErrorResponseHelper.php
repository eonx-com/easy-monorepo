<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\Helper;

use EonX\EasyUtils\Common\Enum\HttpStatusCode;
use EonX\EasyUtils\Common\Helper\ErrorDetailsHelper;
use Swoole\Http\Response;
use Throwable;

final class ErrorResponseHelper
{
    private const string INTERNAL_SERVER_ERROR = 'Internal Server Error';

    public static function getContent(Throwable $throwable, bool $debug = false): string
    {
        if ($debug === false) {
            return (string)\json_encode([
                'error' => HttpStatusCode::InternalServerError->description() ?? self::INTERNAL_SERVER_ERROR,
            ]);
        }

        $details = ['error' => $throwable->getMessage()];

        if (\class_exists(ErrorDetailsHelper::class)) {
            $details = \array_merge(ErrorDetailsHelper::resolveSimpleDetails($throwable), $details);
        }

        return (string)\json_encode($details);
    }

    public static function sendErrorResponse(Throwable $throwable, Response $response, bool $debug = false): void
    {
        $response->status(
            HttpStatusCode::InternalServerError->value,
            HttpStatusCode::InternalServerError->description() ?? self::INTERNAL_SERVER_ERROR
        );

        $response->header('Content-Type', 'application/json');
        $response->end(self::getContent($throwable, $debug));
    }
}
