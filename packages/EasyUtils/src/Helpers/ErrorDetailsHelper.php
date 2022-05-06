<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Helpers;

class ErrorDetailsHelper
{
    /**
     * @return mixed[]
     */
    public static function resolveSimpleDetails(\Throwable $throwable, ?bool $withTrace = null): array
    {
        $details = [
            'code' => $throwable->getCode(),
            'class' => \get_class($throwable),
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
