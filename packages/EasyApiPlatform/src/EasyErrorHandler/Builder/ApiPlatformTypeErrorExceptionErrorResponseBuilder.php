<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use Throwable;
use TypeError;

final class ApiPlatformTypeErrorExceptionErrorResponseBuilder extends
    AbstractApiPlatformSerializerExceptionErrorResponseBuilder
{
    protected function doBuildViolations(Throwable $throwable): array
    {
        $violations = [];

        if (
            $throwable instanceof TypeError
            && \preg_match(
                '/(?<class>.*)::__construct\(\): Argument #\d+ \(\$(?<property>.*)\) must' .
                ' be of type (?<expectedType>.*), .* given/',
                $throwable->getMessage(),
                $matches
            ) === 1
        ) {
            $violations = [
                $this->normalizePropertyName($matches['property'], $matches['class']) => [
                    $this->translator->trans(
                        'violations.invalid_type',
                        [
                            '%expected_type%' => $this->normalizeTypeName($matches['expectedType']),
                        ]
                    ),
                ],
            ];
        }

        return $violations;
    }
}
