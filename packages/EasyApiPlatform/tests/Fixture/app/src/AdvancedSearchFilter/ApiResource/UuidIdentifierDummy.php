<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\AdvancedSearchFilter\ApiResource;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * UUID identifier dummy.
 */
#[ApiResource]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'uuidField' => 'exact',
    'relatedUuidIdentifierDummy' => 'exact',
])]
#[ORM\Entity]
class UuidIdentifierDummy
{
    #[ORM\Column(type: 'uuid')]
    #[ORM\Id]
    private Uuid $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: RelatedUuidIdentifierDummy::class)]
    private RelatedUuidIdentifierDummy $relatedUuidIdentifierDummy;

    #[ORM\Column(type: 'uuid')]
    private Uuid $uuidField;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getRelatedUuidIdentifierDummy(): RelatedUuidIdentifierDummy
    {
        return $this->relatedUuidIdentifierDummy;
    }

    public function getUuidField(): Uuid
    {
        return $this->uuidField;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function setRelatedUuidIdentifierDummy(RelatedUuidIdentifierDummy $relatedUuidIdentifierDummy): void
    {
        $this->relatedUuidIdentifierDummy = $relatedUuidIdentifierDummy;
    }

    public function setUuidField(Uuid $uuidField): void
    {
        $this->uuidField = $uuidField;
    }
}
