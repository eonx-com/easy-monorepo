<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use ApiPlatform\Validator\Exception\ConstraintViolationListAwareExceptionInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

final class ApiPlatformValidationExceptionErrorResponseBuilder extends AbstractApiPlatformExceptionErrorResponseBuilder
{
    protected function buildViolations(Throwable $throwable): array
    {
        $violations = [];

        if ($throwable instanceof ConstraintViolationListAwareExceptionInterface) {
            foreach ($throwable->getConstraintViolationList() as $violation) {
                $propertyPath = $this->normalizePropertyName($violation->getPropertyPath());

                if (isset($violations[$propertyPath]) === false) {
                    $violations[$propertyPath] = [];
                }

                $violations[$propertyPath][] = $this->resolveMessage($violation);
            }
        }

        return $violations;
    }

    private function resolveMessage(ConstraintViolationInterface $violation): string
    {
        $message = (string)$violation->getMessage();

        if (
            \preg_match(
                '/^This value should be of type (?<expectedType>[A-Za-z_\\\\]+[A-Za-z_])\.$/',
                $message,
                $matches
            ) === 1
        ) {
            $message = $this->translator->trans(
                'violations.invalid_type',
                [
                    '%expected_type%' => $this->normalizeTypeName($matches['expectedType']),
                ]
            );
        }

        return $this->resolveMessageFromHint($violation) ?? $message;
    }

    private function resolveMessageFromHint(ConstraintViolationInterface $violation): ?string
    {
        /** @var string|null $hint */
        $hint = $violation->getParameters()['hint'] ?? null;

        if ($hint !== null) {
            if (
                $hint === 'The data is either not an string, an empty string, or null; you should pass a string that' .
                ' can be parsed with the passed format or a valid DateTime string.'
                || \preg_match('/Failed to parse time string \(.*\) at position .* \(.*\): /', $hint) === 1
                || \preg_match(
                    '/Parsing datetime string "[^"]+" using format "[^"]+" resulted in \d error/',
                    $hint
                ) === 1
            ) {
                return $this->translator->trans('violations.invalid_datetime', []);
            }

            if (
                \preg_match('/Nested documents for attribute "\w+" are not allowed. Use IRIs instead./', $hint)
                || \preg_match(
                    '/The type of the "\w+" attribute must be "array" \(nested document\) or "string"' .
                    ' \(IRI\), "\w+" given./',
                    $hint
                )
            ) {
                return $this->translator->trans('violations.invalid_iri', []);
            }

            if (\preg_match('/Item not found for "(.+)"./', $hint)) {
                return $hint;
            }
        }

        return null;
    }
}
