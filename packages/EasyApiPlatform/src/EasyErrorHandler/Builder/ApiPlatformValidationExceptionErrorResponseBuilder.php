<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use ApiPlatform\Validator\Exception\ConstraintViolationListAwareExceptionInterface;
use BackedEnum;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;

final class ApiPlatformValidationExceptionErrorResponseBuilder extends AbstractApiPlatformExceptionErrorResponseBuilder
{
    /**
     * @deprecated Deprecated since 6.4.0, will be moved to the parent class in 7.0
     */
    private IriConverterInterface $iriConverter;

    /**
     * @deprecated Deprecated since 6.4.0, will be moved to the parent class in 7.0
     */
    private RequestStack $requestStack;

    /**
     * @deprecated Deprecated since 6.4.0, will be moved to the parent class in 7.0
     */
    public function buildData(Throwable $throwable, array $data): array
    {
        $violations = $this->buildViolations($throwable);

        if (\count($violations) > 0) {
            $data[$this->getKey('message')] = $this->translator->trans('exceptions.not_valid', []);
            $data[$this->getKey('violations')] = $violations;

            if ($this->validationErrorCode !== null) {
                $data[$this->getKey('code')] = $this->validationErrorCode instanceof BackedEnum
                    ? $this->validationErrorCode->value
                    : $this->validationErrorCode;
            }
        }

        return parent::buildData($throwable, $data);
    }

    #[Required]
    public function setIriConverter(IriConverterInterface $iriConverter): void
    {
        $this->iriConverter = $iriConverter;
    }

    #[Required]
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

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

    /**
     * @deprecated Deprecated since 6.4.0, will be moved to the parent class in 7.0
     */
    protected function normalizePropertyName(string $name, ?string $class = null): string
    {
        if ($class === null) {
            $mainRequest = $this->requestStack->getMainRequest();

            if ($mainRequest !== null) {
                /** @var string|null $apiResourceClass */
                $apiResourceClass = $mainRequest->attributes->get('_api_resource_class');
                $class = $apiResourceClass;
            }
        }

        if ($this->nameConverter !== null && $class !== null) {
            return $this->nameConverter->normalize($name, $class);
        }

        return $name;
    }

    /**
     * @deprecated Deprecated since 6.4.0, will be moved to the parent class in 7.0
     */
    protected function normalizeTypeName(string $class): string
    {
        $typeName = $class;

        if (\class_exists($class) || \interface_exists($class)) {
            try {
                $typeName = $this->iriConverter->getIriFromResource(
                    $class,
                    UrlGeneratorInterface::ABS_PATH,
                    new GetCollection()
                );

                $typeName .= ' IRI';

                if (\str_starts_with($typeName, '/.well-known/genid/')) {
                    $typeName = null;
                }
            } catch (Throwable) {
                // Do nothing
            }

            if ($typeName === null) {
                $classReflection = new ReflectionClass($class);
                $typeName = $classReflection->getShortName();
            }
        }

        return $typeName;
    }

    private function resolveMessage(ConstraintViolationInterface $violation): string
    {
        $message = (string)$violation->getMessage();

        if (
            \preg_match(
                '/^This value should be of type (?<expectedType>[A-Za-z_|\\\\]+[A-Za-z_])\.$/',
                $message,
                $matches
            ) === 1
        ) {
            $message = match (true) {
                \str_contains($matches['expectedType'], 'DateTime')
                || \str_contains($matches['expectedType'], 'Carbon') => $this->translator->trans(
                    'violations.invalid_datetime',
                    []
                ),
                default => $this->translator->trans(
                    'violations.invalid_type',
                    [
                        '%expected_types%' => $this->normalizeTypeName($matches['expectedType']),
                    ]
                )
            };
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

            if (\preg_match('/Invalid IRI "(.+)"./', $hint)) {
                return $hint;
            }

            if (\preg_match('/The data must belong to a backed enumeration of type (.+)/', $hint)) {
                return $this->translator->trans('violations.invalid_enum', []);
            }
        }

        return null;
    }
}
