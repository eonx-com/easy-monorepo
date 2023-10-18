<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\DataTransferObject\Author;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource]
final class Book
{
    #[Assert\Valid]
    public Author $author;

    public Category $category;

    public int $pageCount;

    public DateTimeImmutable $publishedAt;

    public PublishingHouse $publishingHouse;

    public CarbonImmutable $someCarbonImmutableDate;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    public string $title;

    public function __construct(
        private readonly string $description,
        private readonly int $weight,
        public PrintingHouse $printingHouse,
    ) {
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }
}
