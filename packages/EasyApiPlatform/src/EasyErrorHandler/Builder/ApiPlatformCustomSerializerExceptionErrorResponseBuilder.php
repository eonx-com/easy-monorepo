<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Builder;

use BackedEnum;
use EonX\EasyErrorHandler\Common\Translator\TranslatorInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Throwable;

final class ApiPlatformCustomSerializerExceptionErrorResponseBuilder extends
    AbstractApiPlatformSerializerExceptionErrorResponseBuilder
{
    public function __construct(
        TranslatorInterface $translator,
        MetadataAwareNameConverter $nameConverter,
        array $keys,
        ?int $priority = null,
        private readonly array $customSerializerExceptions = [],
        int|string|BackedEnum|null $validationErrorCode = null,
    ) {
        parent::__construct($translator, $nameConverter, $keys, $priority, $validationErrorCode);
    }

    protected function doBuildViolations(Throwable $throwable): array
    {
        foreach ($this->customSerializerExceptions as $exception) {
            if ($throwable::class !== $exception['class']) {
                continue;
            }

            if (\preg_match($exception['message_pattern'], $throwable->getMessage()) === 1) {
                $violation = $this->translator->trans($exception['violation_message'], []);
                if ($throwable instanceof NotNormalizableValueException) {
                    return [
                        $throwable->getPath() => [
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
