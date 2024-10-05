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
                '/.*::__construct\(\): Argument #\d+ \(\$(.*)\) must be of type (.*), (.*) given/',
                $throwable->getMessage(),
                $matches
            ) === 1
        ) {
            $explodedArgumentType = \explode('\\', $matches[2]);
            $violations = [
                $matches[1] => [
                    $this->translator->trans(
                        'violations.another_iri',
                        [
                            '%iri%' => \end($explodedArgumentType),
                        ]
                    ),
                ],
            ];
        }

        return $violations;
    }
}
