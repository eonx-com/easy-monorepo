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

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * @return int|string
     */
    public function getBatchId()
    {
        return $this->batchId;
    }

    public function getDependsOnName(): ?string
    {
        return $this->dependsOnName;
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

    /**
     * @param int|string $batchId
     */
    public function setBatchId($batchId): BatchItemInterface
    {
        $this->batchId = $batchId;

        return $this;
    }

    public function setDependsOnName(string $name): BatchItemInterface
    {
        $this->dependsOnName = $name;

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
            'message' => $this->getMessage() ? \serialize($this->getMessage()) : null,
            'requires_approval' => $this->isApprovalRequired() ? 1 : 0,
        ]);
    }
}
