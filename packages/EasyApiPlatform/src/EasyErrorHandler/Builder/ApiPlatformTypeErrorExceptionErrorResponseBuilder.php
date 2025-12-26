<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;
use TypeError;

final class ApiPlatformTypeErrorExceptionErrorResponseBuilder extends
    AbstractApiPlatformSerializerExceptionErrorResponseBuilder
{
    /**
     * @deprecated Deprecated since 6.4.0, will be moved to the parent class in 7.0
     */
    private IriConverterInterface $iriConverter;

    /**
     * @deprecated Deprecated since 6.4.0, will be moved to the parent class in 7.0
     */
    private RequestStack $requestStack;

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
            /** @var class-string $class */
            $class = $matches['class'];
            $violations = [
                $this->normalizePropertyName($matches['property'], $class) => [
                    $this->translator->trans(
                        'violations.invalid_type',
                        [
                            '%expected_types%' => $this->normalizeTypeName($matches['expectedType']),
                        ]
                    ),
                ],
            ];
        }

        return $violations;
    }

    /**
     * @param class-string|null $class
     *
     * @deprecated Deprecated since 6.4.0, will be moved to the parent class in 7.0
     *
     */
    protected function normalizePropertyName(string $name, ?string $class = null): string
    {
        if ($class === null) {
            $mainRequest = $this->requestStack->getMainRequest();

            if ($mainRequest !== null) {
                /** @var class-string|null $apiResourceClass */
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
}
