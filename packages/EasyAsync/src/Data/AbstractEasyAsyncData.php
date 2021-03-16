<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Data;

use EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface;
use EonX\EasyAsync\Interfaces\EasyAsyncDataInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
abstract class AbstractEasyAsyncData implements EasyAsyncDataInterface
{
    /**
     * @var \DateTime
     */
    protected $finishedAt;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var \DateTime
     */
    protected $startedAt;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var mixed
     */
    protected $targetId;

    /**
     * @var string
     */
    protected $targetType;

    /**
     * @var string
     */
    protected $type;

    public function __construct(TargetInterface $target, string $type)
    {
        $this->targetId = $target->getTargetId();
        $this->targetType = $target->getTargetType();
        $this->type = $type;
    }

    public function getFinishedAt(): ?\DateTime
    {
        return $this->finishedAt;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getStartedAt(): ?\DateTime
    {
        return $this->startedAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    public function getTargetType(): string
    {
        return $this->targetType;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setFinishedAt(\DateTime $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setStartedAt(\DateTime $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        $format = DateTimeGeneratorInterface::DATE_FORMAT;

        return [
            'finished_at' => $this->getFinishedAt() ? $this->getFinishedAt()
                ->format($format) : null,
            'id' => $this->getId(),
            'started_at' => $this->getStartedAt() ? $this->getStartedAt()
                ->format($format) : null,
            'status' => $this->getStatus(),
            'target_id' => $this->getTargetId(),
            'target_type' => $this->getTargetType(),
            'type' => $this->getType(),
        ];
    }
}
