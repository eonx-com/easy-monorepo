<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;

final class CustomSerializerExceptionErrorResponseBuilder extends AbstractDeserializationErrorResponseBuilder
{
    /**
     * @var array<array{class: string, message_pattern: string, violation_message: string}>
     */
    private array $customSerializerExceptions = [];

    /**
     * @param array<array{class: string, message_pattern: string, violation_message: string}> $customSerializerExceptions
     */
    #[Required]
    public function setCustomSerializerExceptions(array $customSerializerExceptions): void
    {
        $this->customSerializerExceptions = $customSerializerExceptions;
    }

    protected function doBuildViolations(Throwable $throwable): array
    {
        foreach ($this->customSerializerExceptions as $exception) {
            if ($throwable::class !== $exception['class']) {
                continue;
            }

            if (\preg_match($exception['message_pattern'], $throwable->getMessage()) === 1) {
                $violation = $this->translator->trans($exception['violation_message'], []);
                $propertyPath = $throwable instanceof NotNormalizableValueException && $throwable->getPath() !== null
                    ? $this->normalizePropertyName($throwable->getPath())
                    : '';

                return [
                    $propertyPath => [
                        $violation,
                    ],
                ];
            }
        }

        return [];
    }
}
