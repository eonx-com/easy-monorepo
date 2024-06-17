<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\ValueObject;

abstract class AbstractBatchItem extends AbstractBatchObject implements BatchItemInterface
{
    private int $attempts = 0;

    private int|string $batchId;

    private ?string $dependsOnName = null;

    private bool $encrypted = false;

    private ?string $encryptionKeyName = null;

    private int $maxAttempts = 1;

    private ?object $message = null;

    public function __construct()
    {
        $this
            ->setStatus(BatchObjectInterface::STATUS_CREATED)
            ->setType(BatchItemInterface::TYPE_MESSAGE);
    }

    public function canBeRetried(): bool
    {
        return $this->getAttempts() < $this->getMaxAttempts();
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function getBatchId(): int|string
    {
        return $this->batchId;
    }

    public function getDependsOnName(): ?string
    {
        return $this->dependsOnName;
    }

    public function getEncryptionKeyName(): ?string
    {
        return $this->encryptionKeyName;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getMessage(): ?object
    {
        return $this->message;
    }

    public function isEncrypted(): bool
    {
        return $this->encrypted;
    }

    public function isPendingApproval(): bool
    {
        if (parent::isPendingApproval()) {
            return true;
        }

        $pendingApprovalStatuses = [
            self::STATUS_BATCH_PENDING_APPROVAL,
            self::STATUS_PROCESSING_DEPENDENT_OBJECTS,
        ];

        return \in_array($this->getStatus(), $pendingApprovalStatuses, true);
    }

    public function isRetried(): bool
    {
        return $this->getAttempts() > 1;
    }

    public function setAttempts(int $attempts): BatchItemInterface
    {
        $this->attempts = $attempts;

        return $this;
    }

    public function setBatchId(int|string $batchId): BatchItemInterface
    {
        $this->batchId = $batchId;

        return $this;
    }

    public function setDependsOnName(string $name): BatchItemInterface
    {
        $this->dependsOnName = $name;

        return $this;
    }

    public function setEncrypted(?bool $encrypted = null): BatchItemInterface
    {
        $this->encrypted = $encrypted ?? true;

        return $this;
    }

    public function setEncryptionKeyName(string $encryptionKeyName): BatchItemInterface
    {
        $this->encryptionKeyName = $encryptionKeyName;

        return $this;
    }

    public function setMaxAttempts(int $maxAttempts): BatchItemInterface
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    public function setMessage(object $message): BatchItemInterface
    {
        $this->message = $message;

        return $this;
    }

    public function toArray(): array
    {
        return \array_merge(parent::toArray(), [
            'attempts' => $this->getAttempts(),
            'batch_id' => $this->getBatchId(),
            'depends_on_name' => $this->getDependsOnName(),
            'encrypted' => $this->isEncrypted() ? 1 : 0,
            'max_attempts' => $this->getMaxAttempts(),
            'message' => $this->getMessage(),
        ]);
    }
}
