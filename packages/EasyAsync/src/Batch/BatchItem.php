<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use EonX\EasyAsync\Interfaces\Batch\BatchItemInterface;

final class BatchItem implements BatchItemInterface
{
    /**
     * @var string
     */
    private $batchId;

    /**
     * @var \DateTimeInterface
     */
    private $finishedAt;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var mixed[]
     */
    private $reasonParams;

    /**
     * @var \DateTimeInterface
     */
    private $startedAt;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $targetClass;

    /**
     * @var \Throwable
     */
    private $throwable;

    public function __construct(string $batchId, string $targetClass, string $id)
    {
        $this->batchId = $batchId;
        $this->targetClass = $targetClass;
        $this->id = $id;
    }

    public function getBatchId(): string
    {
        return $this->batchId;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @return null|mixed[]
     */
    public function getReasonParams(): ?array
    {
        return $this->reasonParams;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTargetClass(): string
    {
        return $this->targetClass;
    }

    public function getThrowable(): ?\Throwable
    {
        return $this->throwable;
    }

    public function setFinishedAt(\DateTimeInterface $finishedAt): BatchItemInterface
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function setId(string $id): BatchItemInterface
    {
        $this->id = $id;

        return $this;
    }

    public function setReason(string $reason): BatchItemInterface
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @param mixed[] $params
     */
    public function setReasonParams(array $params): BatchItemInterface
    {
        $this->reasonParams = $params;

        return $this;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): BatchItemInterface
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function setStatus(string $status): BatchItemInterface
    {
        $this->status = $status;

        return $this;
    }

    public function setThrowable(\Throwable $throwable): BatchItemInterface
    {
        $this->throwable = $throwable;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'batch_id' => $this->getBatchId(),
            'target_class' => $this->getTargetClass(),
            'started_at' => $this->getStartedAt(),
            'finished_at' => $this->getFinishedAt(),
            'status' => $this->getStatus(),
            'reason' => $this->getReason(),
            'reason_params' => $this->getReasonParams(),
            'throwable' => $this->getThrowable(),
        ];
    }
}
