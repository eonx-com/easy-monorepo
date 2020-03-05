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
     * @param mixed $info
     */
    public function addDebugInfo(string $name, $info): self;

    /**
     * @return null|mixed[]
     */
    public function getDebugInfo(): ?array;

    /**
     * @return null|mixed[]
     */
    public function getFailureParams(): ?array;

    public function getFailureReason(): ?string;

    public function getJobId(): string;

    /**
     * @return null|mixed[]
     */
    public function getValidationErrors(): ?array;

    /**
     * @param null|mixed[] $debugInfo
     */
    public function setDebugInfo(?array $debugInfo = null): self;

    /**
     * @param null|mixed[] $failureParams
     */
    public function setFailureParams(?array $failureParams = null): self;

    public function setFailureReason(?string $failureReason = null): self;

    /**
     * @param null|mixed[] $validationErrors
     */
    public function setValidationErrors(?array $validationErrors = null): self;
}
