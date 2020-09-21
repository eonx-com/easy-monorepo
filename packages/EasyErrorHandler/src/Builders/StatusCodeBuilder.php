<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\Exceptions\StatusCodeAwareExceptionInterface;
use Throwable;

final class StatusCodeBuilder extends AbstractErrorResponseBuilder
{
    public function buildStatusCode(Throwable $throwable, ?int $statusCode = null): ?int
    {
        if ($statusCode !== null) {
            return $statusCode;
        }

        if ($throwable instanceof StatusCodeAwareExceptionInterface) {
            return $throwable->getStatusCode();
        }

        return null;
    }
}
