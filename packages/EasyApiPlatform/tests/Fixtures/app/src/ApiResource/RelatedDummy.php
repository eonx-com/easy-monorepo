<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixtures\App\ApiResource;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Carbon\CarbonImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EonX\EasyApiPlatform\Filters\AdvancedSearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource]
#[ORM\Entity]
class RelatedDummy extends ParentDummy
{
    #[Groups(['friends'])]
    #[Orm\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $dummyBoolean;

    #[ApiFilter(DateFilter::class)]
    #[Groups(['friends'])]
    #[Assert\DateTime]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?CarbonImmutable $dummyDate;

    #[ORM\Embedded]
    #[Groups(['friends'])]
    private EmbeddableDummy $embeddedDummy;

    #[ApiProperty(writable: false)]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['chicago', 'friends'])]
    private int $id;

    #[ApiProperty(iris: ['RelatedDummy.name'])]
    #[Groups(['friends'])]
    #[Orm\Column(type: Types::STRING, nullable: true)]
    private ?string $name;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \EonX\EasyApiPlatform\Tests\Stubs\App\ApiResource\RelatedToDummyFriend>
     */
    #[ORM\OneToMany(mappedBy: 'relatedDummy', targetEntity: RelatedToDummyFriend::class, cascade: ['persist'])]
    #[Groups(['fakemanytomany', 'friends'])]
    private Collection $relatedToDummyFriend;

    #[ApiProperty(deprecationReason: 'This property is deprecated for upgrade test')]
    #[ApiFilter(AdvancedSearchFilter::class)]
    #[ORM\Column(type: Types::STRING)]
    #[Groups(['barcelona', 'chicago', 'friends'])]
    private string $symfony = 'symfony';

    #[ORM\ManyToOne(targetEntity: ThirdLevel::class, cascade: ['persist'])]
    #[Groups(['barcelona', 'chicago', 'friends'])]
    private ThirdLevel $thirdLevel;

    public function __construct()
    {
        $this->relatedToDummyFriend = new ArrayCollection();
        $this->embeddedDummy = new EmbeddableDummy();
    }

    public function addRelatedToDummyFriend(RelatedToDummyFriend $relatedToDummyFriend): void
    {
        $this->relatedToDummyFriend->add($relatedToDummyFriend);
    }

    public function getDummyDate(): ?CarbonImmutable
    {
        return $this->dummyDate;
    }

    public function getEmbeddedDummy(): EmbeddableDummy
    {
        return $this->embeddedDummy;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection<int, \EonX\EasyApiPlatform\Tests\Stubs\App\ApiResource\RelatedToDummyFriend>
     */
    public function getRelatedToDummyFriend(): Collection
    {
        return $this->relatedToDummyFriend;
    }

    public function getSymfony(): string
    {
        return $this->symfony;
    }

    public function getThirdLevel(): ?ThirdLevel
    {
        return $this->thirdLevel;
    }

    public function isDummyBoolean(): ?bool
    {
        return $this->dummyBoolean;
    }

    public function setDummyBoolean(?bool $dummyBoolean = null): void
    {
        $this->dummyBoolean = $dummyBoolean;
    }

    public function setDummyDate(?CarbonImmutable $dummyDate = null): void
    {
        $this->dummyDate = $dummyDate;
    }

    public function setEmbeddedDummy(EmbeddableDummy $embeddedDummy): void
    {
        $this->embeddedDummy = $embeddedDummy;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function setSymfony(string $symfony): void
    {
        $this->symfony = $symfony;
    }

    public function setThirdLevel(ThirdLevel $thirdLevel): void
    {
        $this->thirdLevel = $thirdLevel;
    }
}
