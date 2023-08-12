<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Normalizers;

use Carbon\CarbonImmutable;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

final class CarbonImmutableNormalizer extends DateTimeNormalizer
{
    /**
     * @param mixed[]|null $context
     */
    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        ?array $context = null,
    ): CarbonImmutable {
        return new CarbonImmutable(parent::denormalize($data, $type, $format, $context ?? []));
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return $type === CarbonImmutable::class || parent::supportsDenormalization($data, $type, $format);
    }
}
