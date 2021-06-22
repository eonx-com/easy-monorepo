<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchItemInterface extends BatchObjectInterface
{
    /**
     * @var string
     */
    public const STATUS_BATCH_PENDING_APPROVAL = 'batch_pending_approval';

    /**
     * @var string
     */
    public const TYPE_MESSAGE = 'message';

    /**
     * @var string
     */
    public const TYPE_NESTED_BATCH = 'nested_batch';

    public function getAttempts(): int;

    /**
     * @return int|string
     */
    public function getBatchId();

    public function getDependsOnName(): ?string;

    public function getMessage(): ?object;

    public function isApprovalRequired(): bool;

    public function isRetried(): bool;

    public function setApprovalRequired(?bool $approvalRequired = null): self;

    public function setAttempts(int $attempts): self;

    /**
     * @param int|string $batchId
     */
    public function setBatchId($batchId): self;

    public function setDependsOnName(string $name): self;

    public function setMessage(object $message): self;
}
