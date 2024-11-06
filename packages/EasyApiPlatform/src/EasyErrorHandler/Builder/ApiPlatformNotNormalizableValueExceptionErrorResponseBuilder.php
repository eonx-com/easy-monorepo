<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Throwable;

/**
 * @deprecated Deprecated since 6.4.0, will be removed in 7.0
 */
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
