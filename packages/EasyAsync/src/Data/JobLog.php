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
     * Add debug info.
     *
     * @param string $name
     * @param mixed $info
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    public function addDebugInfo(string $name, $info): JobLogInterface
    {
        $debugInfo = $this->debugInfo ?? [];
        $debugInfo[$name] = $info;

        return $this;
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
            'debug_info' => $this->jsonEncode($this->getDebugInfo()),
            'failure_params' => $this->jsonEncode($this->getFailureParams()),
            'failure_reason' => $this->getFailureReason(),
            'job_id' => $this->getJobId(),
            'validation_errors' => $this->jsonEncode($this->getValidationErrors())
        ];

        return parent::toArray() + $array;
    }

    /**
     * Get json representation of given array.
     *
     * @param null|mixed $array
     *
     * @return null|string
     */
    private function jsonEncode(?array $array = null): ?string
    {
        return $array !== null ? \json_encode($array) : null;
    }
}
