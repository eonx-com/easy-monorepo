<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\DataTransferObject\Author;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    openapi: false,
)]
final class Book
{
    #[Assert\Valid]
    public Author $author;

    #[Context(
        normalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'],
        denormalizationContext: [DateTimeNormalizer::FORMAT_KEY => 'Y-m-d|'],
    )]
    public ?DateTimeImmutable $availableFrom = null;

    public Category $category;

    public int $pageCount;

    public DateTimeImmutable $publishedAt;

    public PublishingHouse $publishingHouse;

    public CarbonImmutable $someCarbonImmutableDate;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    public string $title;

    public function __construct(
        public readonly string $description,
        public readonly int $weight,
        public PrintingHouse $printingHouse,
    ) {
    }
}
