<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Throwable;

final class ApiPlatformUnexpectedValueExceptionErrorResponseBuilder extends
    AbstractApiPlatformSerializerExceptionErrorResponseBuilder
{
    protected function doBuildViolations(Throwable $throwable): array
    {
        /** @var array<int, string> $matches */
        $matches = [];
        $violations = [];

        if ($throwable instanceof UnexpectedValueException) {
            $previous = $throwable->getPrevious();
            $violations = match (true) {
                $throwable->getMessage() === 'The input data is misformatted.' => match (true) {
                    $previous instanceof NotNormalizableValueException
                    => $this->buildViolationsForNotNormalizableValueException($previous),
                    default => [
                        $throwable->getMessage(),
                    ]
                },

                \preg_match('/Invalid IRI "(.+)"/', $throwable->getMessage()) === 1 => [
                    $throwable->getMessage(),
                ],

                \preg_match('/Item not found for "(.+)"./', $throwable->getMessage()) === 1 => [
                    $throwable->getMessage(),
                ],

                \preg_match(
                    '/Nested documents for attribute "(.*)" are not allowed. Use IRIs instead./',
                    $throwable->getMessage(),
                    $matches
                ) === 1 => [
                    $matches[1] => [$this->translator->trans('violations.invalid_iri', [])],
                ],

                default => []
            };
        }

        return $violations;
    }
}
