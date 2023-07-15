<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Helpers;

use Throwable;

final class ErrorDetailsHelper
{
    /**
     * @return mixed[]
     */
    public static function resolveSimpleDetails(Throwable $throwable, ?bool $withTrace = null): array
    {
        $details = [
            'code' => $throwable->getCode(),
            'class' => $throwable::class,
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
