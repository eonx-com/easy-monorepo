<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/AdvancedSearchFilter/ApiResource/EmbeddableDummy.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\AdvancedSearchFilter\ApiResource;
========
namespace EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\ApiResource;
>>>>>>>> refs/heads/6.x:packages/EasyApiPlatform/tests/Fixture/app/src/AdvancedSearchFilter/ApiResource/EmbeddableDummy.php

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class EmbeddableDummy
{
    #[Orm\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $dummyBoolean = null;

    #[Assert\DateTime]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?CarbonImmutable $dummyDate = null;

    #[Orm\Column(type: Types::FLOAT, nullable: true)]
    private ?float $dummyFloat = null;

    #[Groups(['embed'])]
    #[Orm\Column(type: Types::STRING, nullable: true)]
    private ?string $dummyName = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $dummyPrice = null;

    #[Groups(['barcelona', 'chicago'])]
    #[Orm\Column(type: Types::STRING, nullable: true)]
    private ?string $symfony = null;

    public function __construct()
    {
    }

    public static function staticMethod(): void
    {
    }

    public function getDummyDate(): ?CarbonImmutable
    {
        return $this->dummyDate;
    }

    public function getDummyFloat(): ?float
    {
        return $this->dummyFloat;
    }

    public function getDummyName(): ?string
    {
        return $this->dummyName;
    }

    public function getDummyPrice(): ?string
    {
        return $this->dummyPrice;
    }

    public function getSymfony(): ?string
    {
        return $this->symfony;
    }

    public function isDummyBoolean(): ?bool
    {
        return $this->dummyBoolean;
    }

    public function setDummyBoolean(bool $dummyBoolean): void
    {
        $this->dummyBoolean = $dummyBoolean;
    }

    public function setDummyDate(CarbonImmutable $dummyDate): void
    {
        $this->dummyDate = $dummyDate;
    }

    public function setDummyFloat(float $dummyFloat): void
    {
        $this->dummyFloat = $dummyFloat;
    }

    public function setDummyName(string $dummyName): void
    {
        $this->dummyName = $dummyName;
    }

    public function setDummyPrice(string $dummyPrice): void
    {
        $this->dummyPrice = $dummyPrice;
    }

    public function setSymfony(?string $symfony = null): void
    {
        $this->symfony = $symfony;
    }
}
