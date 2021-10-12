<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use DateTimeInterface;

class ActivityLogEntry
{
    public const ACTION_CREATE = 'create';

    public const ACTION_DELETE = 'delete';

    public const ACTION_UPDATE = 'update';

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
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @var string|null
     */
    private $data;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $oldData;

    /**
     * @var string|null
     */
    private $subjectId;

    /**
     * @var string
     */
    private $subjectType;

    /**
     * @var DateTimeInterface
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

    public function getSubjectId(): ?string
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

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setData(?string $data = null): self
    {
        $this->data = $data;

        return $this;
    }

    public function setOldData(?string $oldData = null): self
    {
        $this->oldData = $oldData;

        return $this;
    }

    public function setSubjectId(?string $subjectId = null): self
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    public function setSubjectType(string $subjectType): self
    {
        $this->subjectType = $subjectType;

        return $this;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
