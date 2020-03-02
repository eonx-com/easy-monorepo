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
     * Create job log from given array.
     *
     * @param mixed[] $data
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     *
     * @throws \Nette\Utils\JsonException
     */
    public static function fromArray(array $data): JobLogInterface
    {
        $jobLog = new static(new Target($data['target_id'], $data['target_type']), $data['type'], $data['job_id']);

        $jobLog->setStatus($data['status']);
        $jobLog->setId($data['id']);

        PropertyHelper::setJsonProperties($jobLog, $data, ['debug_info', 'failure_params', 'validation_errors']);
        PropertyHelper::setOptionalProperties($jobLog, $data, ['failure_reason']);

        return $jobLog;
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

        $this->debugInfo = $debugInfo;

        return $this;
    }

    /**
     * Get debug info.
     *
     * @return null|mixed[]
     */
    public function getDebugInfo(): ?array
    {
        return $this->debugInfo;
    }

    /**
     * Get failure params.
     *
     * @return null|mixed[]
     */
    public function getFailureParams(): ?array
    {
        return $this->failureParams;
    }

    /**
     * Get failure reason, most of the time it would be a translation key.
     *
     * @return null|string
     */
    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    /**
     * Get job id the log belongs to.
     *
     * @return string
     */
    public function getJobId(): string
    {
        return $this->jobId;
    }

    /**
     * If any validation failed during process, errors can be stored here.
     *
     * @return null|mixed[]
     */
    public function getValidationErrors(): ?array
    {
        return $this->validationErrors;
    }

    /**
     * Set debug info.
     *
     * @param null|mixed[] $debugInfo
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    public function setDebugInfo(?array $debugInfo = null): JobLogInterface
    {
        $this->debugInfo = $debugInfo;

        return $this;
    }

    /**
     * Set failure params.
     *
     * @param null|mixed[] $failureParams
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    public function setFailureParams(?array $failureParams = null): JobLogInterface
    {
        $this->failureParams = $failureParams;

        return $this;
    }

    /**
     * Set failure reason.
     *
     * @param null|string $failureReason
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    public function setFailureReason(?string $failureReason = null): JobLogInterface
    {
        $this->failureReason = $failureReason;

        return $this;
    }

    /**
     * Set validation errors.
     *
     * @param null|mixed[] $validationErrors
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    public function setValidationErrors(?array $validationErrors = null): JobLogInterface
    {
        $this->validationErrors = $validationErrors;

        return $this;
    }

    /**
     * Get array representation.
     *
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
            'validation_errors' => JsonHelper::encode($this->getValidationErrors())
        ];

        return parent::toArray() + $array;
    }
}
