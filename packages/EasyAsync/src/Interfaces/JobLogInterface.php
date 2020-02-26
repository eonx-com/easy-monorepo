<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface JobLogInterface extends EasyAsyncDataInterface
{
    public const STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_IN_PROGRESS
    ];

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_IN_PROGRESS = 'in_progress';

    /**
     * Add debug info.
     *
     * @param string $name
     * @param mixed $info
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    public function addDebugInfo(string $name, $info): self;

    /**
     * Get debug info.
     *
     * @return null|mixed[]
     */
    public function getDebugInfo(): ?array;

    /**
     * Get failure params.
     *
     * @return null|mixed[]
     */
    public function getFailureParams(): ?array;

    /**
     * Get failure reason, most of the time it would be a translation key.
     *
     * @return null|string
     */
    public function getFailureReason(): ?string;

    /**
     * Get job id the log belongs to.
     *
     * @return string
     */
    public function getJobId(): string;

    /**
     * If any validation failed during process, errors can be stored here.
     *
     * @return null|mixed[]
     */
    public function getValidationErrors(): ?array;

    /**
     * Set debug info.
     *
     * @param mixed[] $debugInfo
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    public function setDebugInfo(array $debugInfo): self;

    /**
     * Set failure params.
     *
     * @param mixed[] $failureParams
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    public function setFailureParams(array $failureParams): self;

    /**
     * Set failure reason.
     *
     * @param string $failureReason
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    public function setFailureReason(string $failureReason): self;

    /**
     * Set validation errors.
     *
     * @param mixed[] $validationErrors
     *
     * @return \EonX\EasyAsync\Interfaces\JobLogInterface
     */
    public function setValidationErrors(array $validationErrors): self;

}
