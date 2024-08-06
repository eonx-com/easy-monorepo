<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\ValueObject;

use DateTimeInterface;
use EonX\EasyBatch\Common\Enum\BatchObjectStatus;
use Throwable;

interface BatchObjectInterface
{
    public function getCancelledAt(): ?DateTimeInterface;

    public function getCreatedAt(): ?DateTimeInterface;

    public function getFinishedAt(): ?DateTimeInterface;

    public function getId(): int|string|null;

    /**
     * @throws \EonX\EasyBatch\Common\Exception\BatchObjectIdRequiredException
     */
    public function getIdOrFail(): int|string;

    public function getMetadata(): ?array;

    public function getName(): ?string;

    public function getStartedAt(): ?DateTimeInterface;

    public function getStatus(): BatchObjectStatus;

    public function getThrowable(): ?Throwable;

    public function getThrowableDetails(): ?array;

    public function getType(): ?string;

    public function getUpdatedAt(): ?DateTimeInterface;

    public function isApprovalRequired(): bool;

    public function isCancelled(): bool;

    public function isCompleted(): bool;

    public function isFailed(): bool;

    public function isPendingApproval(): bool;

    public function isSucceeded(): bool;

    public function setApprovalRequired(?bool $approvalRequired = null): self;

    public function setCancelledAt(DateTimeInterface $cancelledAt): self;

    public function setCreatedAt(DateTimeInterface $createdAt): self;

    public function setFinishedAt(DateTimeInterface $finishedAt): self;

    public function setId(int|string $id): self;

    public function setMetadata(array $metadata): self;

    public function setName(?string $name = null): self;

    public function setStartedAt(DateTimeInterface $startedAt): self;

    public function setStatus(BatchObjectStatus $status): self;

    public function setThrowable(Throwable $throwable): self;

    public function setThrowableDetails(array $throwableDetails): self;

    public function setType(string $type): self;

    public function setUpdatedAt(DateTimeInterface $updatedAt): self;

    public function toArray(): array;
}
