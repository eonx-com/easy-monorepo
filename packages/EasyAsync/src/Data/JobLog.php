<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Data;

use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;

final class JobLog extends AbstractEasyAsyncData implements JobLogInterface
{
    /**
     * @var mixed[]
     */
    protected $debugInfo;

    /**
     * @var mixed[]
     */
    protected $failureParams;

    /**
     * @var string
     */
    protected $failureReason;

    /**
     * @var string
     */
    protected $jobId;

    /**
     * @var mixed[]
     */
    protected $validationErrors;

    /**
     * JobLog constructor.
     *
     * @param \EonX\EasyAsync\Interfaces\TargetInterface $target
     * @param string $type
     * @param string $jobId
     */
    public function __construct(TargetInterface $target, string $type, string $jobId)
    {
        parent::__construct($target, $type);

        $this->jobId = $jobId;
        $this->status = self::STATUS_IN_PROGRESS;
    }

    /**
     * @inheritDoc
     */
    public function getDebugInfo(): ?array
    {
        return $this->debugInfo;
    }

    /**
     * @inheritDoc
     */
    public function getFailureParams(): ?array
    {
        return $this->failureParams;
    }

    /**
     * @inheritDoc
     */
    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    /**
     * @inheritDoc
     */
    public function getJobId(): string
    {
        return $this->jobId;
    }

    /**
     * @inheritDoc
     */
    public function getValidationErrors(): ?array
    {
        return $this->validationErrors;
    }

    /**
     * @inheritDoc
     */
    public function setDebugInfo(array $debugInfo): JobLogInterface
    {
        $this->debugInfo = $debugInfo;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFailureParams(array $failureParams): JobLogInterface
    {
        $this->failureParams = $failureParams;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setFailureReason(string $failureReason): JobLogInterface
    {
        $this->failureReason = $failureReason;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setValidationErrors(array $validationErrors): JobLogInterface
    {
        $this->validationErrors = $validationErrors;

        return $this;
    }

    /**
     * Get array representation.
     *
     * @return mixed[]
     */
    public function toArray(): array
    {
        $array = [
            'debug_info' => $this->getDebugInfo(),
            'failure_params' => $this->getFailureParams(),
            'failure_reason' => $this->getFailureReason(),
            'job_id' => $this->getJobId(),
            'validation_errors' => $this->getValidationErrors()
        ];

        return parent::toArray() + $array;
    }
}
