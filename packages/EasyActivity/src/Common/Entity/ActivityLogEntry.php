<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Entity;

use DateTimeInterface;
use EonX\EasyActivity\Common\Enum\ActivityAction;
use EonX\EasyActivity\Common\ValueObject\ActivitySubjectDataInterface;

final class ActivityLogEntry
{
    /**
     * The type of action performed on the subject.
     */
    private ActivityAction $action;

    /**
     * An optional identifier for an actor in the application.
     */
    private ?string $actorId = null;

    /**
     * An optional name for an actor in the application.
     */
    private ?string $actorName = null;

    /**
     * A mandatory actor type. The actor type could be a `user`, `provider`, `customer`, `jwt:provider`,
     * `api_key:customer`, or something similar in an application. The default value is
     * `ActivityLogEntry::DEFAULT_ACTOR_TYPE` (i.e. `system`).
     */
    private string $actorType;

    /**
     * Set to "now" by the default store implementation.
     */
    private DateTimeInterface $createdAt;

    /**
     * An optional representation of the state of the subject after applying the action (i.e. a serialized
     * entity/model containing the new attribute values of the subject after updating the entity/model).
     * This is a simple key-value array with attribute names in keys.
     */
    private ?string $subjectData = null;

    /**
     * An optional identifier for a subject in the application.
     */
    private string $subjectId;

    /**
     * An optional representation of the state of the subject before applying the action (i.e. a serialized
     * entity/model containing the original attribute values before updating the entity/model).
     * This is a simple key-value array with attribute names in keys.
     */
    private ?string $subjectOldData = null;

    /**
     * A mandatory subject type in the application. The subject type can be a short class name, a FQCN (Fully
     * Qualified Class Name), or any arbitrary string that an application maps in the package configuration.
     */
    private string $subjectType;

    /**
     * Set to "now" by the default store implementation.
     */
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

    public function setSubjectData(ActivitySubjectDataInterface $subjectData): self
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
