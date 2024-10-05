<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

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
                $violations[$this->nameConverter->normalize($argument, $throwable->getClass())] = [
                    $this->translator->trans('violations.missing_constructor_argument', []),
                ];
            }
        }

        return $violations;
    }
}
