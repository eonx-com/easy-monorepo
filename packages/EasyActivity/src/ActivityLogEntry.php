<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use DateTimeInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectDataInterface;
use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;
use EonX\EasyActivity\Interfaces\ActorInterface;

final class ActivityLogEntry
{
    /**
     * @var string
     */
    public const ACTION_CREATE = 'create';

    /**
     * @var string
     */
    public const ACTION_DELETE = 'delete';

    /**
     * @var string
     */
    public const ACTION_UPDATE = 'update';

    /**
     * @var string
     */
    public const DEFAULT_ACTOR_TYPE = 'system';

    /**
     * @var string
     */
    private $action;

    /**
     * @var string|null
     */
    private $actorId;

    /**
     * @var string|null
     */
    private $actorName;

    /**
     * @var string
     */
    private $actorType;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var string|null
     */
    private $data;

    /**
     * @var string|null
     */
    private $oldData;

    /**
     * @var string
     */
    private $subjectId;

    /**
     * @var string
     */
    private $subjectType;

    /**
     * @var \DateTimeInterface
     */
    private $updatedAt;

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

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function getOldData(): ?string
    {
        return $this->oldData;
    }

    public function getSubjectId(): string
    {
        return $this->subjectId;
    }

    public function getSubjectType(): string
    {
        return $this->subjectType;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setAction(string $action): self
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

    public function setSubjectData(ActivitySubjectDataInterface $subjectData): self
    {
        $this->data = $subjectData->getData();
        $this->oldData = $subjectData->getOldData();

        return $this;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
