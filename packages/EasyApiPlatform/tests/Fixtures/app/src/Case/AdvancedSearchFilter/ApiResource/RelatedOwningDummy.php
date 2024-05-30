<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\AdvancedSearchFilter\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(types: ['https://schema.org/Product'])]
#[ORM\Entity]
class RelatedOwningDummy
{
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Id]
    private int $id;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $name = null;

    #[ORM\OneToOne(mappedBy: 'relatedOwningDummy', targetEntity: Dummy::class, cascade: ['persist'])]
    private ?Dummy $ownedDummy = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getOwnedDummy(): ?Dummy
    {
        return $this->ownedDummy;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function setOwnedDummy(?Dummy $ownedDummy): void
    {
        $this->ownedDummy = $ownedDummy;

        if ($this->ownedDummy !== null && $this !== $this->ownedDummy->getRelatedOwningDummy()) {
            $this->ownedDummy->setRelatedOwningDummy($this);
        }
    }
}
