<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Builder;

use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Throwable;

final class ApiPlatformMissingConstructorArgumentsExceptionErrorResponseBuilder extends
    AbstractApiPlatformSerializerExceptionErrorResponseBuilder
{
    protected function doBuildViolations(Throwable $throwable): array
    {
        $violations = [];

        if ($throwable instanceof MissingConstructorArgumentsException) {
            foreach ($throwable->getMissingConstructorArguments() as $argument) {
                $violations[$argument] = [
                    $this->translator->trans('violations.missing_constructor_argument', []),
                ];
            }
        }

        return $violations;
    }
}
