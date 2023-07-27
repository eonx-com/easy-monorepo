<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixtures\App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(types: ['https://schema.org/Product'])]
#[ORM\Entity]
class RelatedOwnedDummy
{
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Id]
    private int $id;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $name = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\OneToOne(inversedBy: 'relatedOwnedDummy', targetEntity: Dummy::class, cascade: ['persist'])]
    private Dummy $owningDummy;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get owning dummy.
     */
    public function getOwningDummy(): ?Dummy
    {
        return $this->owningDummy;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function setOwningDummy(Dummy $owningDummy): void
    {
        $this->owningDummy = $owningDummy;
    }
}
