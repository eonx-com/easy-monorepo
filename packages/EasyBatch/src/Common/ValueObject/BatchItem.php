<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\ValueObject;

use EonX\EasyBatch\Common\Enum\BatchItemType;
use EonX\EasyBatch\Common\Enum\BatchObjectStatus;

final class BatchItem extends AbstractBatchObject
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
            ->setStatus(BatchObjectStatus::Created)
            ->setType(BatchItemType::Message->value);
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

        return \in_array($this->getStatus(), BatchObjectStatus::STATUSES_FOR_PENDING_APPROVAL, true);
    }

    public function isRetried(): bool
    {
        return $this->getAttempts() > 1;
    }

    public function setAttempts(int $attempts): self
    {
        $this->attempts = $attempts;

        return $this;
    }

    public function setBatchId(int|string $batchId): self
    {
        $this->batchId = $batchId;

        return $this;
    }

    public function setDependsOnName(string $name): self
    {
        $this->dependsOnName = $name;

        return $this;
    }

    public function setEncrypted(?bool $encrypted = null): self
    {
        $this->encrypted = $encrypted ?? true;

        return $this;
    }

    public function setEncryptionKeyName(string $encryptionKeyName): self
    {
        $this->encryptionKeyName = $encryptionKeyName;

        return $this;
    }

    public function setMaxAttempts(int $maxAttempts): self
    {
        $this->maxAttempts = $maxAttempts;

        return $this;
    }

    public function setMessage(object $message): BatchItem
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
