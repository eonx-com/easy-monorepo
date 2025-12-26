<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\Normalizer;

use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class CarbonImmutableNormalizer implements NormalizerInterface, DenormalizerInterface
{
    private const array SUPPORTED_TYPES = [
        CarbonImmutable::class => true,
        DateTime::class => true,
        DateTimeImmutable::class => true,
        DateTimeInterface::class => true,
    ];

    public function __construct(
        #[Autowire(service: 'serializer.normalizer.datetime')]
        private DateTimeNormalizer $dateTimeNormalizer,
    ) {
    }

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        ?array $context = null,
    ): CarbonImmutable {
        return new CarbonImmutable(
            $this->dateTimeNormalizer->denormalize($data, DateTimeImmutable::class, $format, $context ?? [])
        );
    }

    public function getSupportedTypes(?string $format): array
    {
        return self::SUPPORTED_TYPES;
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
        return isset(self::SUPPORTED_TYPES[$type]);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, ?array $context = null): bool
    {
        return $data instanceof DateTimeInterface;
    }
}
