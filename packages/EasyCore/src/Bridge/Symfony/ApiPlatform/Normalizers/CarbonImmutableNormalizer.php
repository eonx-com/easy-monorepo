<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Normalizers;

use Carbon\CarbonImmutable;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

final class CarbonImmutableNormalizer extends DateTimeNormalizer
{
    /**
     * @param mixed $data
     * @param mixed[]|null $context
     */
    public function denormalize(
        $data,
        string $type,
        ?string $format = null,
        ?array $context = null
    ): ?CarbonImmutable {
        return CarbonImmutable::parse(parent::denormalize($data, $type, $format, $context ?? []));
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
