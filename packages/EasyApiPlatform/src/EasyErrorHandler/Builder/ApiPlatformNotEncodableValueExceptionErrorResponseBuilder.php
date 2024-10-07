<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Throwable;

final class ApiPlatformNotEncodableValueExceptionErrorResponseBuilder extends
    AbstractApiPlatformSerializerExceptionErrorResponseBuilder
{
    protected function doBuildViolations(Throwable $throwable): array
    {
        $violations = [];

        if ($throwable instanceof NotEncodableValueException) {
            $violations = [
                $this->translator->trans('violations.not_encodable', []),
            ];
        }

        return $violations;
    }
}
