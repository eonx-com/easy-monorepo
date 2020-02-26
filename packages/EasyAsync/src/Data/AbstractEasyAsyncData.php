<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Data;

use EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface;
use EonX\EasyAsync\Interfaces\EasyAsyncDataInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;

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

    /**
     * AbstractEasyAsyncData constructor.
     *
     * @param \EonX\EasyAsync\Interfaces\TargetInterface $target
     * @param string $type
     */
    public function __construct(TargetInterface $target, string $type)
    {
        $this->targetId = $target->getTargetId();
        $this->targetType = $target->getTargetType();
        $this->type = $type;
    }

    /**
     * @inheritDoc
     */
    public function getFinishedAt(): ?\DateTime
    {
        return $this->finishedAt;
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getStartedAt(): ?\DateTime
    {
        return $this->startedAt;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * @inheritDoc
     */
    public function getTargetType(): string
    {
        return $this->targetType;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set finishedAt.
     *
     * @param \DateTime $finishedAt
     *
     * @return void
     */
    public function setFinishedAt(\DateTime $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }

    /**
     * @inheritDoc
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * Set startedAt.
     *
     * @param \DateTime $startedAt
     *
     * @return void
     */
    public function setStartedAt(\DateTime $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * Get array representation.
     *
     * @return mixed[]
     */
    public function toArray(): array
    {
        $format = DateTimeGeneratorInterface::DATE_FORMAT;

        return [
            'finished_at' => $this->getFinishedAt() ? $this->getFinishedAt()->format($format) : null,
            'id' => $this->getId(),
            'started_at' => $this->getStartedAt() ? $this->getStartedAt()->format($format) : null,
            'status' => $this->getStatus(),
            'target_id' => $this->getTargetId(),
            'target_type' => $this->getTargetType(),
            'type' => $this->getType()
        ];
    }
}
