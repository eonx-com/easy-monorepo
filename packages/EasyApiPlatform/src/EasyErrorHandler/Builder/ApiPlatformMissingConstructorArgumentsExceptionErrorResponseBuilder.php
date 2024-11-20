<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Throwable;

/**
 * @deprecated Deprecated since 6.4.0, will be removed in 7.0
 */
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
