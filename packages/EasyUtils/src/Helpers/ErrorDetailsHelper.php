<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Helpers;

use Throwable;

final class ErrorDetailsHelper
{
    public static function resolveSimpleDetails(Throwable $throwable, ?bool $withTrace = null): array
    {
        $details = [
            'class' => $throwable::class,
            'code' => $throwable->getCode(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'message' => $throwable->getMessage(),
        ];

        if ($withTrace ?? true) {
            $details['trace'] = \array_map(static function (array $trace): array {
                unset($trace['args']);

                return $trace;
            }, $throwable->getTrace());
        }

        return $details;
    }
}
