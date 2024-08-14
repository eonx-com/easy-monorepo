<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Entity;

use DateTimeInterface;
use EonX\EasyActivity\Common\Enum\ActivityAction;
use EonX\EasyActivity\Common\ValueObject\ActivitySubjectData;

final class ActivityLogEntry
{
    private ActivityAction $action;

    private ?string $actorId = null;

    private ?string $actorName = null;

    private string $actorType;

    private DateTimeInterface $createdAt;

    private ?string $subjectData = null;

    private string $subjectId;

    private ?string $subjectOldData = null;

    private string $subjectType;

    private DateTimeInterface $updatedAt;

    public function getAction(): ActivityAction
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

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
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

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setAction(ActivityAction $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function setActor(ActorInterface $actor): self
    {
        $this->actorId = $actor->getActorId();
        $this->actorName = $actor->getActorName();
        $this->actorType = $actor->getActorType();

        return $this;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setSubject(ActivitySubjectInterface $subject): self
    {
        $this->subjectId = $subject->getActivitySubjectId();
        $this->subjectType = $subject->getActivitySubjectType();

        return $this;
    }

    public function setSubjectData(ActivitySubjectData $subjectData): self
    {
        $this->subjectData = $subjectData->getSubjectData();
        $this->subjectOldData = $subjectData->getSubjectOldData();

        return $this;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
