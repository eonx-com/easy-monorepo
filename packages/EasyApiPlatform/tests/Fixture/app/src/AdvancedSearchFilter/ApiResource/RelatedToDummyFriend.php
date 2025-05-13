<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    openapi: false,
)]
#[ORM\Entity]
class RelatedToDummyFriend
{
    #[Groups(['fakemanytomany', 'friends'])]
    #[Orm\Column(type: Types::STRING, nullable: true)]
    private ?string $description = null;

    #[Assert\NotNull]
    #[Groups(['fakemanytomany', 'friends'])]
    #[ORM\Id]
    #[ORM\JoinColumn(name: 'dummyfriend_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: DummyFriend::class)]
    private DummyFriend $dummyFriend;

    #[ApiProperty(types: ['https://schema.org/name'])]
    #[Assert\NotBlank]
    #[Groups(['fakemanytomany', 'friends'])]
    #[Orm\Column(type: Types::STRING)]
    private string $name;

    #[Assert\NotNull]
    #[ORM\Id]
    #[ORM\JoinColumn(name: 'relateddummy_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: RelatedDummy::class, inversedBy: 'relatedToDummyFriend')]
    private RelatedDummy $relatedDummy;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDummyFriend(): DummyFriend
    {
        return $this->dummyFriend;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRelatedDummy(): RelatedDummy
    {
        return $this->relatedDummy;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setDummyFriend(DummyFriend $dummyFriend): void
    {
        $this->dummyFriend = $dummyFriend;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setRelatedDummy(RelatedDummy $relatedDummy): void
    {
        $this->relatedDummy = $relatedDummy;
    }
}
