<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Math\Normalizer;

use EonX\EasyUtils\Math\ValueObject\Number;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class IntegerNumberNormalizer implements DenormalizerInterface, NormalizerInterface
{
    /**
     * @param string|null $data
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, ?array $context = null): ?Number
    {
        if ($data === null) {
            return null;
        }

        return new Number($data);
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [
            Number::class => true,
        ];
    }

    /**
     * @param \EonX\EasyUtils\Math\ValueObject\Number|null $object
     */
    public function normalize(mixed $object, ?string $format = null, ?array $context = null): string
    {
        if ($object instanceof Number === false) {
            throw new InvalidArgumentException('The object type must be "Number".');
        }

        return (string)$object;
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        ?array $context = null,
    ): bool {
        return ($data === null || \is_string($data)) && $type === Number::class;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, ?array $context = null): bool
    {
        return $data instanceof Number;
    }
}
