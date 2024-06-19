<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixture\App\Entity;

use Carbon\CarbonImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EonX\EasyDoctrine\Traits\TimestampableTrait;
use Stringable;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Index(columns: ['subject_id'])]
#[ORM\Index(columns: ['subject_type'])]
#[ORM\Table(name: 'activity_logs')]
class ActivityLog implements Stringable
{
    use TimestampableTrait;

    /**
     * Timestamp of entity creation.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected CarbonImmutable $createdAt;

    /**
     * Unique identifier of the entity.
     */
    #[ORM\Column(type: 'uuid')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Id]
    protected Uuid $id;

    /**
     * Timestamp of the most recent entity update.
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected CarbonImmutable $updatedAt;

    /**
     * Action that was performed.
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $action;

    /**
     * Identifier of actor that performed the action.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $actorId = null;

    /**
     * Name of actor that performed the action.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $actorName = null;

    /**
     * Type of actor that performed the action.
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $actorType;

    /**
     * Data for the subject after the action was performed.
     */
    #[ORM\Column(type: TYPES::TEXT, nullable: true)]
    private ?string $subjectData = null;

    /**
     * Identifier of subject that was acted upon.
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $subjectId;

    /**
     * Data for the subject before the action was performed.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $subjectOldData = null;

    /**
     * Type of subject that was acted upon.
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $subjectType;

    public function __toString(): string
    {
        return (string)$this->id;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getActorId(): ?string
    {
        return $this->actorId;
    }

    public function getActorName(): ?string
    {
        return $this->actorName;
    }

    public function getActorType(): string
    {
        return $this->actorType;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSubjectData(): ?string
    {
        return $this->subjectData;
    }

    public function getSubjectId(): string
    {
        return $this->subjectId;
    }

    public function getSubjectOldData(): ?string
    {
        return $this->subjectOldData;
    }

    public function getSubjectType(): string
    {
        return $this->subjectType;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function setActorId(?string $actorId = null): self
    {
        $this->actorId = $actorId;

        return $this;
    }

    public function setActorName(?string $actorName = null): self
    {
        $this->actorName = $actorName;

        return $this;
    }

    public function setActorType(string $actorType): self
    {
        $this->actorType = $actorType;

        return $this;
    }

    public function setCreatedAt(CarbonImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function setSubjectData(?string $subjectData = null): self
    {
        $this->subjectData = $subjectData;

        return $this;
    }

    public function setSubjectId(string $subjectId): self
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    public function setSubjectOldData(?string $subjectOldData = null): self
    {
        $this->subjectOldData = $subjectOldData;

        return $this;
    }

    public function setSubjectType(string $subjectType): self
    {
        $this->subjectType = $subjectType;

        return $this;
    }

    public function setUpdatedAt(CarbonImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
