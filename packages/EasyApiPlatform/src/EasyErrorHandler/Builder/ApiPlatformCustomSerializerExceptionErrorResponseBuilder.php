<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;

final class ApiPlatformCustomSerializerExceptionErrorResponseBuilder extends
    AbstractApiPlatformSerializerExceptionErrorResponseBuilder
{
    private array $customSerializerExceptions = [];

    /**
     * @deprecated Deprecated since 6.4.0, will be moved to the parent class in 7.0
     */
    private RequestStack $requestStack;

    #[Required]
    public function setCustomSerializerExceptions(array $customSerializerExceptions): void
    {
        $this->customSerializerExceptions = $customSerializerExceptions;
    }

    #[Required]
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    protected function doBuildViolations(Throwable $throwable): array
    {
        foreach ($this->customSerializerExceptions as $exception) {
            if ($throwable::class !== $exception['class']) {
                continue;
            }

            if (\preg_match($exception['message_pattern'], $throwable->getMessage()) === 1) {
                $violation = $this->translator->trans($exception['violation_message'], []);

                if ($throwable instanceof NotNormalizableValueException && $throwable->getPath() !== null) {
                    return [
                        $this->normalizePropertyName($throwable->getPath()) => [
                            $violation,
                        ],
                    ];
                }

                return [
                    $violation,
                ];
            }
        }

        return [];
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
}
