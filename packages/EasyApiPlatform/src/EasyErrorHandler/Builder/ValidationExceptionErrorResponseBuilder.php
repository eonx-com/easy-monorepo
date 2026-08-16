<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use ApiPlatform\Validator\Exception\ConstraintViolationListAwareExceptionInterface;
use ReflectionClass;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;

final class ValidationExceptionErrorResponseBuilder extends AbstractApiPlatformErrorResponseBuilder
{
    private IriConverterInterface $iriConverter;

    #[Required]
    public function setIriConverter(IriConverterInterface $iriConverter): void
    {
        $this->iriConverter = $iriConverter;
    }

    protected function buildViolations(Throwable $throwable): array
    {
        $violations = [];

        if ($throwable instanceof ConstraintViolationListAwareExceptionInterface) {
            foreach ($throwable->getConstraintViolationList() as $violation) {
                $propertyPath = $this->normalizePropertyName($violation->getPropertyPath());
                $violations[$propertyPath][] = $this->resolveMessage($violation);
            }
        }

        return $violations;
    }

    private function normalizeTypeName(string $class): string
    {
        if (\class_exists($class) === false && \interface_exists($class) === false) {
            return $class;
        }

        try {
            $iri = $this->iriConverter->getIriFromResource(
                $class,
                UrlGeneratorInterface::ABS_PATH,
                new GetCollection()
            );
        } catch (Throwable) {
            return $class;
        }

        if ($iri === null || \str_starts_with($iri, '/.well-known/genid/')) {
            $classReflection = new ReflectionClass($class);

            return $classReflection->getShortName();
        }

        return $iri . ' IRI';
    }

    private function resolveMessage(ConstraintViolationInterface $violation): string
    {
        $messageFromHint = $this->resolveMessageFromHint($violation);

        if ($messageFromHint !== null) {
            return $messageFromHint;
        }

        $message = (string)$violation->getMessage();

        if (
            \preg_match(
                '/^This value should be of type (?<expectedType>[A-Za-z_|\\\\]+[A-Za-z_])\.$/',
                $message,
                $matches
            ) !== 1
        ) {
            return $message;
        }

        if (
            \str_contains($matches['expectedType'], 'DateTime')
            || \str_contains($matches['expectedType'], 'Carbon')
        ) {
            return $this->translator->trans('violations.invalid_datetime', []);
        }

        return $this->translator->trans(
            'violations.invalid_type',
            [
                '%expected_types%' => $this->normalizeTypeName($matches['expectedType']),
            ]
        );
    }

    private function resolveMessageFromHint(ConstraintViolationInterface $violation): ?string
    {
        /** @var string|null $hint */
        $hint = $violation->getParameters()['hint'] ?? null;

        if ($hint === null) {
            return null;
        }

        if (
            $hint === 'The data is either not an string, an empty string, or null; you should pass a string that'
            . ' can be parsed with the passed format or a valid DateTime string.'
            || \preg_match('/Failed to parse time string \(.*\) at position .* \(.*\): /', $hint) === 1
            || \preg_match(
                '/Parsing datetime string "[^"]+" using format "[^"]+" resulted in \d error/',
                $hint
            ) === 1
        ) {
            return $this->translator->trans('violations.invalid_datetime', []);
        }

        if (
            \preg_match('/Nested documents for attribute "\w+" are not allowed. Use IRIs instead./', $hint) === 1
            || \preg_match(
                '/The type of the "\w+" attribute must be "array" \(nested document\) or "string"'
                . ' \(IRI\), "\w+" given./',
                $hint
            ) === 1
        ) {
            return $this->translator->trans('violations.invalid_iri', []);
        }

        if (\preg_match('/^Item not found for "(.+)"\.$/', $hint, $matches) === 1) {
            return $this->translator->trans('violations.item_not_found', [
                '%iri%' => $matches[1],
            ]);
        }

        if (\preg_match('/^Invalid IRI "(.+)"\.$/', $hint, $matches) === 1) {
            return $this->translator->trans('violations.invalid_iri_value', [
                '%iri%' => $matches[1],
            ]);
        }

        if (\preg_match('/The data must belong to a backed enumeration of type (.+)/', $hint) === 1) {
            return $this->translator->trans('violations.invalid_enum', []);
        }

        return null;
    }
}
