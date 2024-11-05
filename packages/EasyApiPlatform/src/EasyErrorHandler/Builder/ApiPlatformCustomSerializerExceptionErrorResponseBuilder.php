<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Contracts\Service\Attribute\Required;
use Throwable;

final class ApiPlatformCustomSerializerExceptionErrorResponseBuilder extends
    AbstractApiPlatformSerializerExceptionErrorResponseBuilder
{
    private array $customSerializerExceptions = [];

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
}
