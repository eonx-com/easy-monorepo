<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Normalizer;

use Carbon\CarbonImmutable;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class CarbonImmutableNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private DateTimeNormalizer $dateTimeNormalizer,
    ) {
    }

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        ?array $context = null,
    ): CarbonImmutable {
        return new CarbonImmutable($this->dateTimeNormalizer->denormalize($data, $type, $format, $context ?? []));
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            CarbonImmutable::class => true,
        ];
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function normalize(
        mixed $object,
        ?string $format = null,
        array $context = [],
    ): int|float|string {
        return $this->dateTimeNormalizer->normalize($object, $format, $context);
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        ?array $context = null,
    ): bool {
        return $type === CarbonImmutable::class
            || $this->dateTimeNormalizer->supportsDenormalization($data, $type, $format, $context ?? []);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, ?array $context = null): bool
    {
        return $this->dateTimeNormalizer->supportsNormalization($data, $format, $context ?? []);
    }
}
