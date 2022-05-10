<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

use EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException;
use EonX\EasyBatch\Interfaces\BatchObjectInterface;

abstract class AbstractBatchObject implements BatchObjectInterface
{
    private bool $approvalRequired = false;

    private ?\DateTimeInterface $cancelledAt = null;

    private ?\DateTimeInterface $createdAt = null;

    private ?\DateTimeInterface $finishedAt = null;

    private int|string|null $id = null;

    /**
     * @var mixed[]|null
     */
    private ?array $metadata = null;

    private ?string $name = null;

    private ?\DateTimeInterface $startedAt = null;

    private string $status = self::STATUS_PENDING;

    private ?\Throwable $throwable = null;

    /**
     * @var mixed[]|null
     */
    private ?array $throwableDetails = null;

    private ?string $type = null;

    private ?\DateTimeInterface $updatedAt = null;

    public function getCancelledAt(): ?\DateTimeInterface
    {
        return $this->cancelledAt;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function getId(): int|string|null
    {
        return $this->id;
    }

    /**
     * @throws \EonX\EasyBatch\Exceptions\BatchObjectIdRequiredException
     */
    public function getIdOrFail(): int|string
    {
        if ($this->getId() !== null) {
            return $this->getId();
        }

        throw new BatchObjectIdRequiredException(\sprintf('ID not set on batchObject "%s"', static::class));
    }

    /**
     * @return null|mixed[]
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getName(): ?string
    {
        return $this->name;
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

    /**
     * @return null|mixed[]
     */
    public function getThrowableDetails(): ?array
    {
        return $this->throwableDetails;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function isApprovalRequired(): bool
    {
        return $this->approvalRequired;
    }

    public function isCancelled(): bool
    {
        return $this->getStatus() === self::STATUS_CANCELLED;
    }

    public function isCompleted(): bool
    {
        return \in_array(
            $this->getStatus(),
            [self::STATUS_FAILED, self::STATUS_SUCCEEDED, self::STATUS_CANCELLED],
            true
        );
    }

    public function isFailed(): bool
    {
        return $this->getStatus() === self::STATUS_FAILED;
    }

    public function isPendingApproval(): bool
    {
        return $this->getStatus() === self::STATUS_SUCCEEDED_PENDING_APPROVAL;
    }

    public function isSucceeded(): bool
    {
        return $this->getStatus() === self::STATUS_SUCCEEDED;
    }

    public function setApprovalRequired(?bool $approvalRequired = null): BatchObjectInterface
    {
        $this->approvalRequired = $approvalRequired ?? true;

        return $this;
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

    public function setId(int|string $id): BatchObjectInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param mixed[] $metadata
     */
    public function setMetadata(array $metadata): BatchObjectInterface
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function setName(?string $name = null): BatchObjectInterface
    {
        $this->name = $name;

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

    /**
     * @param mixed[] $throwableDetails
     */
    public function setThrowableDetails(array $throwableDetails): BatchObjectInterface
    {
        $this->throwableDetails = $throwableDetails;

        return $this;
    }

    public function setType(string $type): BatchObjectInterface
    {
        $this->type = $type;

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
            'class' => static::class,
            'cancelled_at' => $this->getCancelledAt(),
            'created_at' => $this->getCreatedAt(),
            'id' => $this->getId(),
            'metadata' => $this->getMetadata(),
            'name' => $this->getName(),
            'finished_at' => $this->getFinishedAt(),
            'requires_approval' => $this->isApprovalRequired() ? 1 : 0,
            'started_at' => $this->getStartedAt(),
            'status' => $this->getStatus(),
            'throwable' => $this->getThrowable(),
            'type' => $this->getType(),
            'updated_at' => $this->getUpdatedAt(),
        ];
    }
}
