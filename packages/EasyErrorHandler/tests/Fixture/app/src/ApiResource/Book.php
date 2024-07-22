<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/ApiResource/Book.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\ApiResource;
========
namespace EonX\EasyErrorHandler\Tests\Fixture\App\ApiResource;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/ApiResource/Book.php

use ApiPlatform\Metadata\ApiResource;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/ApiResource/Book.php
use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\DataTransferObject\Author;
========
use EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject\Author;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/ApiResource/Book.php
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
