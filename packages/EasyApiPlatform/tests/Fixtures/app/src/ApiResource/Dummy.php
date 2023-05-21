<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixtures\App\ApiResource;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Carbon\CarbonImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource]
#[ApiFilter(SearchFilter::class)]
#[ORM\Entity]
class Dummy
{
    #[Orm\Column(type: Types::STRING, nullable: true)]
    #[ApiProperty(types: ['https://schema.org/alternateName'])]
    private ?string $alias;

    /**
     * @var mixed[]|null
     */
    #[Orm\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private ?array $arrayData;

    #[ApiProperty(types: ['https://schema.org/description'])]
    #[Orm\Column(type: Types::STRING, nullable: true)]
    private ?string $description;

    #[Orm\Column(type: Types::STRING, nullable: true)]
    private ?string $dummy;

    #[Orm\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $dummyBoolean;

    #[ApiProperty(types: ['https://schema.org/DateTime'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?CarbonImmutable $dummyDate;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $dummyFloat;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $dummyPrice;

    /**
     * @var mixed[]
     */
    private array $foo;

    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    /**
     * @var mixed[]|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $jsonData;

    #[Orm\Column(type: Types::STRING, nullable: true)]
    #[ApiProperty(types: ['https://schema.org/name'])]
    #[Assert\NotBlank]
    private ?string $name;

    #[Orm\Column(type: Types::STRING, nullable: true)]
    private ?string $nameConverted;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \EonX\EasyApiPlatform\Tests\Fixtures\App\ApiResource\RelatedDummy> Several dummies
     */
    #[ORM\ManyToMany(targetEntity: RelatedDummy::class)]
    private Collection $relatedDummies;

    #[ORM\ManyToOne(targetEntity: RelatedDummy::class)]
    #[ApiProperty(push: true)]
    private ?RelatedDummy $relatedDummy;

    #[ORM\OneToOne(mappedBy: 'owningDummy', targetEntity: RelatedOwnedDummy::class, cascade: ['persist'])]
    private ?RelatedOwnedDummy $relatedOwnedDummy;

    #[ORM\OneToOne(inversedBy: 'ownedDummy', targetEntity: RelatedOwningDummy::class, cascade: ['persist'])]
    private ?RelatedOwningDummy $relatedOwningDummy;

    public function __construct()
    {
        $this->relatedDummies = new ArrayCollection();
        $this->jsonData = [];
        $this->arrayData = [];
    }

    public static function staticMethod(): void
    {
    }

    public function addRelatedDummy(RelatedDummy $relatedDummy): void
    {
        $this->relatedDummies->add($relatedDummy);
    }

    public function fooBar(mixed $baz): void
    {
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @return mixed[]|null
     */
    public function getArrayData(): ?array
    {
        return $this->arrayData;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDummy(): ?string
    {
        return $this->dummy;
    }

    public function getDummyDate(): ?CarbonImmutable
    {
        return $this->dummyDate;
    }

    public function getDummyFloat(): ?float
    {
        return $this->dummyFloat;
    }

    public function getDummyPrice(): ?string
    {
        return $this->dummyPrice;
    }

    /**
     * @return mixed[]
     */
    public function getFoo(): array
    {
        return $this->foo;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return mixed[]|null
     */
    public function getJsonData(): ?array
    {
        return $this->jsonData;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameConverted(): ?string
    {
        return $this->nameConverted;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<int, \EonX\EasyApiPlatform\Tests\Fixtures\App\ApiResource\RelatedDummy>
     */
    public function getRelatedDummies(): Collection
    {
        return $this->relatedDummies;
    }

    public function getRelatedDummy(): ?RelatedDummy
    {
        return $this->relatedDummy;
    }

    public function getRelatedOwnedDummy(): ?RelatedOwnedDummy
    {
        return $this->relatedOwnedDummy;
    }

    public function getRelatedOwningDummy(): ?RelatedOwningDummy
    {
        return $this->relatedOwningDummy;
    }

    public function isDummyBoolean(): ?bool
    {
        return $this->dummyBoolean;
    }

    public function setAlias(?string $alias = null): void
    {
        $this->alias = $alias;
    }

    /**
     * @param mixed[]|null $arrayData
     */
    public function setArrayData(?array $arrayData = null): void
    {
        $this->arrayData = $arrayData;
    }

    public function setDescription(?string $description = null): void
    {
        $this->description = $description;
    }

    public function setDummy(?string $dummy = null): void
    {
        $this->dummy = $dummy;
    }

    public function setDummyBoolean(?bool $dummyBoolean = null): void
    {
        $this->dummyBoolean = $dummyBoolean;
    }

    public function setDummyDate(CarbonImmutable $dummyDate = null): void
    {
        $this->dummyDate = $dummyDate;
    }

    public function setDummyFloat(?float $dummyFloat): void
    {
        $this->dummyFloat = $dummyFloat;
    }

    public function setDummyPrice(?string $dummyPrice = null): void
    {
        $this->dummyPrice = $dummyPrice;
    }

    /**
     * @param mixed[] $foo
     */
    public function setFoo(array $foo): void
    {
        $this->foo = $foo;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param mixed[]|null $jsonData
     */
    public function setJsonData(?array $jsonData = null): void
    {
        $this->jsonData = $jsonData;
    }

    public function setName(?string $name = null): void
    {
        $this->name = $name;
    }

    public function setNameConverted(?string $nameConverted): void
    {
        $this->nameConverted = $nameConverted;
    }

    public function setRelatedDummy(?RelatedDummy $relatedDummy = null): void
    {
        $this->relatedDummy = $relatedDummy;
    }

    public function setRelatedOwnedDummy(?RelatedOwnedDummy $relatedOwnedDummy = null): void
    {
        $this->relatedOwnedDummy = $relatedOwnedDummy;

        if ($this->relatedOwnedDummy !== null && $this !== $this->relatedOwnedDummy->getOwningDummy()) {
            $this->relatedOwnedDummy->setOwningDummy($this);
        }
    }

    public function setRelatedOwningDummy(?RelatedOwningDummy $relatedOwningDummy = null): void
    {
        $this->relatedOwningDummy = $relatedOwningDummy;
    }
}
