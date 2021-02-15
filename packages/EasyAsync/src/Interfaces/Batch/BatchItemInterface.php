<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchItemInterface
{
    /**
     * @var string
     */
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * @var string
     */
    public const STATUS_FAILED = 'failed';

    /**
     * @var string
     */
    public const STATUS_SUCCESS = 'success';

    public function getBatchId(): string;

    public function getFinishedAt(): ?\DateTimeInterface;

    public function getId(): ?string;

    public function getReason(): ?string;

    /**
     * @return null|mixed[]
     */
    public function getReasonParams(): ?array;

    public function getStartedAt(): ?\DateTimeInterface;

    public function getStatus(): string;

    public function getTargetClass(): string;

    public function getThrowable(): ?\Throwable;

    public function setFinishedAt(\DateTimeInterface $finishedAt): self;

    public function setId(string $id): self;

    public function setReason(string $reason): self;

    /**
     * @param mixed[] $params
     */
    public function setReasonParams(array $params): self;

    public function setStartedAt(\DateTimeInterface $startedAt): self;

    public function setStatus(string $status): self;

    public function setThrowable(\Throwable $throwable): self;
}
