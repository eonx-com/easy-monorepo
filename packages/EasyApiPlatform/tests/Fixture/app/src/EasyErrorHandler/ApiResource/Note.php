<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ApiResource]
final class Note
{
    public function __construct(
        public string $text,
        #[Context(
            normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'],
            denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d|'],
        )]
        public DateTimeImmutable $publishedAt,
    ) {
    }
}
