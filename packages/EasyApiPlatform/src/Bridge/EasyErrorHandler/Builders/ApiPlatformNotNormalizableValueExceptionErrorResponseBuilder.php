<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Builders;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Throwable;

final class ApiPlatformNotNormalizableValueExceptionErrorResponseBuilder extends
    AbstractApiPlatformSerializerExceptionErrorResponseBuilder
{
    protected function doBuildViolations(Throwable $throwable): array
    {
        if ($throwable instanceof NotNormalizableValueException) {
            return $this->buildViolationsForNotNormalizableValueException($throwable);
        }

        return [];
    }
}
