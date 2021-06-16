<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

use EonX\EasyBatch\Interfaces\BatchObjectInterface;

abstract class AbstractBatchObject implements BatchObjectInterface
{
    /**
     * @var \DateTimeInterface
     */
    private $cancelledAt;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     */
    private $finishedAt;

    /**
     * @var int|string
     */
    private $id;

    /**
     * @var \DateTimeInterface
     */
    private $startedAt;

    /**
     * @var string
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var \Throwable
     */
    private $throwable;

    /**
     * @var \DateTimeInterface
     */
    private $updatedAt;

    public function getCancelledAt(): ?\DateTimeInterface
    {
        return $this->cancelledAt;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    /**
     * @return null|int|string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getThrowable(): ?\Throwable
    {
        return $this->throwable;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function isCompleted(): bool
    {
        return \in_array($this->getStatus(), [self::STATUS_FAILED, self::STATUS_SUCCEEDED], true);
    }

    public function setCancelledAt(\DateTimeInterface $cancelledAt): BatchObjectInterface
    {
        $this->cancelledAt = $cancelledAt;

        return $this;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): BatchObjectInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setFinishedAt(\DateTimeInterface $finishedAt): BatchObjectInterface
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    /**
     * @param int|string $id
     */
    public function setId($id): BatchObjectInterface
    {
        $this->id = $id;

        return $this;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): BatchObjectInterface
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function setStatus(string $status): BatchObjectInterface
    {
        $this->status = $status;

        return $this;
    }

    public function setThrowable(\Throwable $throwable): BatchObjectInterface
    {
        $this->throwable = $throwable;

        return $this;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): BatchObjectInterface
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'class' => \get_class($this),
            'cancelled_at' => $this->getCancelledAt(),
            'created_at' => $this->getCreatedAt(),
            'id' => $this->getId(),
            'finished_at' => $this->getFinishedAt(),
            'started_at' => $this->getStartedAt(),
            'status' => $this->getStatus(),
            'throwable' => $this->getThrowable(),
            'updated_at' => $this->getUpdatedAt(),
        ];
    }
}
