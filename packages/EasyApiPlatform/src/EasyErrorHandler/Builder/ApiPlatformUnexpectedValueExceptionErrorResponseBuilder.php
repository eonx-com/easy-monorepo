<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use ReflectionMethod;
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
                /**
                 * @deprecated Deprecated since 6.4.0, will be removed in 7.0
                 */
                $throwable->getMessage() === 'The input data is misformatted.' => match (true) {
                    $previous instanceof NotNormalizableValueException
                    => $this->buildViolationsForNotNormalizableValueException($previous),
                    default => [
                        $throwable->getMessage(),
                    ]
                },

                \preg_match('/Invalid IRI "(.+)"/', $throwable->getMessage()) === 1
                => $this->buildViolationForInvalidIri($throwable),

                /**
                 * @deprecated Deprecated since 6.4.0, will be removed in 7.0
                 */
                \preg_match('/Item not found for "(.+)"./', $throwable->getMessage()) === 1 => [
                    $throwable->getMessage(),
                ],

                /**
                 * @deprecated Deprecated since 6.4.0, will be removed in 7.0
                 */
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

    private function buildViolationForInvalidIri(UnexpectedValueException $throwable): array
    {
        $frame = $throwable->getTrace()[0];

//        foreach ($trace as $frame) {
        if (isset($frame['class']) && isset($frame['function'])) {
            $method = new ReflectionMethod($frame['class'], $frame['function']);
            $params = $method->getParameters();
            $args = $frame['args'] ?? [];

            $namedArgs = [];
            foreach ($params as $index => $param) {
                $paramName = $param->getName();
                $paramValue = $args[$index] ?? null;
                $namedArgs[$paramName] = $paramValue;
            }

            print_r($namedArgs);
        }
//        }

        $message = $this->translator->trans('violations.invalid_iri', []);

        if (
            isset($throwable->getTrace()[0]['args'])
            && \is_array($throwable->getTrace()[0]['args'][3])
        ) {
            $path = $throwable->getTrace()[0]['args'][3]['deserialization_path'] ?? null;

            if ($path !== null) {
                return [
                    $path => [
                        $message,
                    ],
                ];
            }
        }

        return [
            $message,
        ];
    }
}
