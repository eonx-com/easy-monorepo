<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Helpers;

use EonX\EasyErrorHandler\Interfaces\Exceptions\StatusCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\SubCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\ValidationExceptionInterface;
use Throwable;

final class ErrorDetailsHelper
{
    /**
     * @return mixed[]
     */
    public static function getDetails(Throwable $throwable, ?bool $extended = null): array
    {
        $extended = $extended ?? true;

        $details = [
            'code' => $throwable->getCode(),
            'class' => \get_class($throwable),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'message' => $throwable->getMessage(),
        ];

        if ($extended) {
            $previous = $throwable->getPrevious();
            if ($previous instanceof Throwable) {
                $details['previous'] = self::getDetails($previous, false);
            }

            $details['trace'] = \array_map(static function (array $trace): array {
                unset($trace['args']);

                return $trace;
            }, $throwable->getTrace());
        }

        if ($throwable instanceof SubCodeAwareExceptionInterface) {
            $details['sub_code'] = $throwable->getSubCode();
        }

        if ($throwable instanceof StatusCodeAwareExceptionInterface) {
            $details['status_code'] = $throwable->getStatusCode();
        }

        if ($throwable instanceof TranslatableExceptionInterface) {
            $details['message_params'] = $throwable->getMessageParams();
            $details['user_message'] = $throwable->getUserMessage();
            $details['user_message_params'] = $throwable->getUserMessageParams();
        }

        if ($throwable instanceof ValidationExceptionInterface) {
            $details['violations'] = $throwable->getErrors();
        }

        return $details;
    }
}
