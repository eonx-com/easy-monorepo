<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Serializer;

use EonX\EasyUtils\ValueObject\Number;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class IntegerNumberNormalizer implements
    CacheableSupportsMethodInterface,
    DenormalizerInterface,
    NormalizerInterface
{
    /**
     * @param string|null $data
     * @param array<string, mixed> $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, ?array $context = null): ?Number
    {
        if ($data === null) {
            return null;
        }

        return new Number($data);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param \EonX\EasyUtils\ValueObject\Number|null $object
     * @param array<string, mixed>|null $context
     */
    public function normalize(mixed $object, ?string $format = null, ?array $context = null): string
    {
        if ($object instanceof Number === false) {
            throw new InvalidArgumentException('The object type must be "Number".');
        }

        return (string)$object;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return ($data === null || \is_string($data)) && $type === Number::class;
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof Number;
    }
}
