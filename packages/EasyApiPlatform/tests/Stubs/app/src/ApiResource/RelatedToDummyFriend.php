<?php

declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Stubs\App\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource]
#[ORM\Entity]
class RelatedToDummyFriend
{
    #[Orm\Column(type: Types::STRING, nullable: true)]
    #[Groups(['fakemanytomany', 'friends'])]
    private ?string $description;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: DummyFriend::class)]
    #[ORM\JoinColumn(name: 'dummyfriend_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['fakemanytomany', 'friends'])]
    #[Assert\NotNull]
    private DummyFriend $dummyFriend;

    #[Orm\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    #[ApiProperty(types: ['https://schema.org/name'])]
    #[Groups(['fakemanytomany', 'friends'])]
    private string $name;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: RelatedDummy::class, inversedBy: 'relatedToDummyFriend')]
    #[ORM\JoinColumn(name: 'relateddummy_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
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
