<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface EasyAsyncDataInterface
{
    /**
     * Get datetime the job finished at.
     *
     * @return null|\DateTime
     */
    public function getFinishedAt(): ?\DateTime;

    /**
     * Get job id.
     *
     * @return null|string
     */
    public function getId(): ?string;

    /**
     * Get datetime the job started at.
     *
     * @return null|\DateTime
     */
    public function getStartedAt(): ?\DateTime;

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * Get target id.
     *
     * @return mixed
     */
    public function getTargetId();

    /**
     * Get target type.
     *
     * @return string
     */
    public function getTargetType(): string;

    /**
     * Get job type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Set finishedAt.
     *
     * @param \DateTime $finishedAt
     *
     * @return void
     */
    public function setFinishedAt(\DateTime $finishedAt): void;

    /**
     * Set job id.
     *
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id): void;

    /**
     * Set startedAt.
     *
     * @param \DateTime $startedAt
     *
     * @return void
     */
    public function setStartedAt(\DateTime $startedAt): void;

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return void
     */
    public function setStatus(string $status): void;

    /**
     * Get array representation.
     *
     * @return mixed[]
     */
    public function toArray(): array;
}
