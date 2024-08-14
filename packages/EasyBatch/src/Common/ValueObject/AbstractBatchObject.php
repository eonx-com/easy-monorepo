<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\ValueObject;

use DateTimeInterface;
use EonX\EasyBatch\Common\Enum\BatchObjectStatus;
use EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException;
use Throwable;

abstract class AbstractBatchObject
{
    private bool $approvalRequired = false;

    private ?DateTimeInterface $cancelledAt = null;

    private ?DateTimeInterface $createdAt = null;

    private ?DateTimeInterface $finishedAt = null;

    private int|string|null $id = null;

    private ?array $metadata = null;

    private ?string $name = null;

    private ?DateTimeInterface $startedAt = null;

    private BatchObjectStatus $status = BatchObjectStatus::Pending;

    private ?Throwable $throwable = null;

    private ?array $throwableDetails = null;

    private ?string $type = null;

    private ?DateTimeInterface $updatedAt = null;

    public function getCancelledAt(): ?DateTimeInterface
    {
        return $this->cancelledAt;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getFinishedAt(): ?DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function getId(): int|string|null
    {
        return $this->id;
    }

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function getIdOrFail(): int|string
    {
        if ($this->getId() !== null) {
            return $this->getId();
        }

        throw new BatchObjectIdRequiredException(\sprintf('ID not set on batchObject "%s"', static::class));
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getStartedAt(): ?DateTimeInterface
    {
        return $this->startedAt;
    }

    public function getStatus(): BatchObjectStatus
    {
        return $this->status;
    }

    public function getThrowable(): ?Throwable
    {
        return $this->throwable;
    }

    public function getThrowableDetails(): ?array
    {
        return $this->throwableDetails;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function isApprovalRequired(): bool
    {
        return $this->approvalRequired;
    }

    public function isCancelled(): bool
    {
        return $this->getStatus() === BatchObjectStatus::Cancelled;
    }

    public function isCompleted(): bool
    {
        return \in_array($this->getStatus(), BatchObjectStatus::STATUSES_FOR_COMPLETE, true);
    }

    public function isFailed(): bool
    {
        return $this->getStatus() === BatchObjectStatus::Failed;
    }

    public function isPendingApproval(): bool
    {
        return $this->getStatus() === BatchObjectStatus::SucceededPendingApproval;
    }

    public function isSucceeded(): bool
    {
        return $this->getStatus() === BatchObjectStatus::Succeeded;
    }

    public function setApprovalRequired(?bool $approvalRequired = null): static
    {
        $this->approvalRequired = $approvalRequired ?? true;

        return $this;
    }

    public function setCancelledAt(DateTimeInterface $cancelledAt): static
    {
        $this->cancelledAt = $cancelledAt;

        return $this;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setFinishedAt(DateTimeInterface $finishedAt): static
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function setId(int|string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function setName(?string $name = null): static
    {
        $this->name = $name;

        return $this;
    }

    public function setStartedAt(DateTimeInterface $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function setStatus(BatchObjectStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function setThrowable(Throwable $throwable): static
    {
        $this->throwable = $throwable;

        return $this;
    }

    public function setThrowableDetails(array $throwableDetails): static
    {
        $this->throwableDetails = $throwableDetails;

        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'cancelled_at' => $this->getCancelledAt(),
            'class' => static::class,
            'created_at' => $this->getCreatedAt(),
            'finished_at' => $this->getFinishedAt(),
            'id' => $this->getId(),
            'metadata' => $this->getMetadata(),
            'name' => $this->getName(),
            'requires_approval' => $this->isApprovalRequired() ? 1 : 0,
            'started_at' => $this->getStartedAt(),
            'status' => $this->getStatus(),
            'throwable' => $this->getThrowable(),
            'type' => $this->getType(),
            'updated_at' => $this->getUpdatedAt(),
        ];
    }
}
