<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchObjectInterface
{
    /**
     * @var string
     */
    public const DATETIME_FORMAT = 'Y-m-d H:i:s.u';

    /**
     * @var string[]
     */
    public const DATE_TIMES = [
        'cancelled_at' => 'setCancelledAt',
        'finished_at' => 'setFinishedAt',
        'started_at' => 'setStartedAt',
        'created_at' => 'setCreatedAt',
        'updated_at' => 'setUpdatedAt',
    ];

    /**
     * @var string
     */
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * @var string
     */
    public const STATUS_FAILED = 'failed';

    /**
     * @var string
     */
    public const STATUS_PENDING = 'pending';

    /**
     * @var string
     */
    public const STATUS_PROCESSING = 'processing';

    /**
     * @var string
     */
    public const STATUS_SUCCEEDED = 'succeeded';

    /**
     * @var string
     */
    public const STATUS_SUCCEEDED_PENDING_APPROVAL = 'succeeded_pending_approval';

    public function getCancelledAt(): ?\DateTimeInterface;

    public function getCreatedAt(): ?\DateTimeInterface;

    public function getFinishedAt(): ?\DateTimeInterface;

    /**
     * @return null|int|string
     */
    public function getId();

    /**
     * @return null|mixed[]
     */
    public function getMetadata(): ?array;

    public function getName(): ?string;

    public function getStartedAt(): ?\DateTimeInterface;

    public function getStatus(): string;

    public function getThrowable(): ?\Throwable;

    public function getType(): ?string;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function isCancelled(): bool;

    public function isCompleted(): bool;

    public function isFailed(): bool;

    public function isSucceeded(): bool;

    public function setCancelledAt(\DateTimeInterface $cancelledAt): self;

    public function setCreatedAt(\DateTimeInterface $createdAt): self;

    public function setFinishedAt(\DateTimeInterface $finishedAt): self;

    /**
     * @param int|string $id
     */
    public function setId($id): self;

    /**
     * @param mixed[] $metadata
     */
    public function setMetadata(array $metadata): self;

    public function setName(?string $name = null): self;

    public function setStartedAt(\DateTimeInterface $startedAt): self;

    public function setStatus(string $status): self;

    public function setThrowable(\Throwable $throwable): self;

    public function setType(string $type): self;

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self;

    /**
     * @return mixed[]
     */
    public function toArray(): array;
}
