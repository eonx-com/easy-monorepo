<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Data;

use EonX\EasyAsync\Helpers\JsonHelper;
use EonX\EasyAsync\Helpers\PropertyHelper;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;

final class JobLog extends AbstractEasyAsyncData implements JobLogInterface
{
    /**
     * @var null|mixed[]
     */
    protected $debugInfo;

    /**
     * @var null|mixed[]
     */
    protected $failureParams;

    /**
     * @var null|string
     */
    protected $failureReason;

    /**
     * @var string
     */
    protected $jobId;

    /**
     * @var null|mixed[]
     */
    protected $validationErrors;

    public function __construct(TargetInterface $target, string $type, string $jobId)
    {
        parent::__construct($target, $type);

        $this->jobId = $jobId;
        $this->status = self::STATUS_IN_PROGRESS;
    }

    /**
     * @param mixed[] $data
     *
     * @throws \Nette\Utils\JsonException
     */
    public static function fromArray(array $data): JobLogInterface
    {
        $jobLog = new self(new Target($data['target_id'], $data['target_type']), $data['type'], $data['job_id']);

        $jobLog->setStatus($data['status']);
        $jobLog->setId($data['id']);

        PropertyHelper::setJsonProperties($jobLog, $data, ['debug_info', 'failure_params', 'validation_errors']);
        PropertyHelper::setOptionalProperties($jobLog, $data, ['failure_reason']);

        return $jobLog;
    }

    /**
     * @param mixed $info
     */
    public function addDebugInfo(string $name, $info): JobLogInterface
    {
        $debugInfo = $this->debugInfo ?? [];
        $debugInfo[$name] = $info;

        $this->debugInfo = $debugInfo;

        return $this;
    }

    /**
     * @return null|mixed[]
     */
    public function getDebugInfo(): ?array
    {
        return $this->debugInfo;
    }

    /**
     * @return null|mixed[]
     */
    public function getFailureParams(): ?array
    {
        return $this->failureParams;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    /**
     * @return null|mixed[]
     */
    public function getValidationErrors(): ?array
    {
        return $this->validationErrors;
    }

    /**
     * @param null|mixed[] $debugInfo
     */
    public function setDebugInfo(?array $debugInfo = null): JobLogInterface
    {
        $this->debugInfo = $debugInfo;

        return $this;
    }

    /**
     * @param null|mixed[] $failureParams
     */
    public function setFailureParams(?array $failureParams = null): JobLogInterface
    {
        $this->failureParams = $failureParams;

        return $this;
    }

    public function setFailureReason(?string $failureReason = null): JobLogInterface
    {
        $this->failureReason = $failureReason;

        return $this;
    }

    /**
     * @param null|mixed[] $validationErrors
     */
    public function setValidationErrors(?array $validationErrors = null): JobLogInterface
    {
        $this->validationErrors = $validationErrors;

        return $this;
    }

    /**
     * @return mixed[]
     *
     * @throws \Nette\Utils\JsonException
     */
    public function toArray(): array
    {
        $array = [
            'debug_info' => JsonHelper::encode($this->getDebugInfo()),
            'failure_params' => JsonHelper::encode($this->getFailureParams()),
            'failure_reason' => $this->getFailureReason(),
            'job_id' => $this->getJobId(),
            'validation_errors' => JsonHelper::encode($this->getValidationErrors()),
        ];

        return parent::toArray() + $array;
    }
}
