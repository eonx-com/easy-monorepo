<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\Normalizer;

use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\DataTransferObject\Isbn;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Deliberately throws a pathless exception, unlike CarbonNormalizer. Covers the custom_serializer_exceptions
 * mapping and the root property path fallback - do not "fix" it to throw NotNormalizableValueException.
 */
final class IsbnNormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, ?array $context = null): Isbn
    {
        if (\is_string($data) && \preg_match('/^\d{13}$/', $data) === 1) {
            return new Isbn($data);
        }

        throw new UnexpectedValueException('Custom message from custom IsbnNormalizer.');
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Isbn::class => true,
        ];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === Isbn::class;
    }
}
