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
    private $reason;

    /**
     * @var mixed[]
     */
    private $reasonParams;

    /**
     * @var bool
     */
    private $approvalRequired = false;

    /**
     * @var string
     */
    private $targetClass;

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

    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @return null|mixed[]
     */
    public function getReasonParams(): ?array
    {
        return $this->reasonParams;
    }

    public function getTargetClass(): string
    {
        return $this->targetClass;
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

    public function setReason(string $reason): BatchItemInterface
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @param mixed[] $params
     */
    public function setReasonParams(array $params): BatchItemInterface
    {
        $this->reasonParams = $params;

        return $this;
    }

    public function setTargetClass(string $targetClass): BatchItemInterface
    {
        $this->targetClass = $targetClass;

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
            'target_class' => $this->getTargetClass(),
            'reason' => $this->getReason(),
            'reason_params' => $this->getReasonParams(),
        ]);
    }
}
