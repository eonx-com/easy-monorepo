<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

use EonX\EasyBatch\Interfaces\BatchItemInterface;

abstract class AbstractBatchItem extends AbstractBatchObject implements BatchItemInterface
{
    /**
     * @var int
     */
    private $attempts = 0;

    /**
     * @var int|string
     */
    private $batchId;

    /**
     * @var string
     */
    private $dependsOnName;

    /**
     * @var int
     */
    private $maxAttempts = 1;

    /**
     * @var object
     */
    private $message;

    /**
     * @var bool
     */
    private $approvalRequired = false;

    public function __construct()
    {
        $this->setType(BatchItemInterface::TYPE_MESSAGE);
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

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getMessage(): ?object
    {
        return $this->message;
    }

    public function isApprovalRequired(): bool
    {
        return $this->approvalRequired;
    }

    public function isRetried(): bool
    {
        return $this->getAttempts() > 1;
    }

    public function setApprovalRequired(?bool $approvalRequired = null): BatchItemInterface
    {
        $this->approvalRequired = $approvalRequired ?? true;

        return $this;
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

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return \array_merge(parent::toArray(), [
            'attempts' => $this->getAttempts(),
            'batch_id' => $this->getBatchId(),
            'depends_on_name' => $this->getDependsOnName(),
            'max_attempts' => $this->getMaxAttempts(),
            'message' => $this->getMessage(),
            'requires_approval' => $this->isApprovalRequired() ? 1 : 0,
        ]);
    }
}
