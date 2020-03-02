<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface WithProcessJobLogDataInterface
{
    /**
     * Get process job log data.
     *
     * @return \EonX\EasyAsync\Interfaces\ProcessJobLogDataInterface
     */
    public function getProcessJobLogData(): ProcessJobLogDataInterface;

    /**
     * Set job id.
     *
     * @param string $jobId
     *
     * @return void
     */
    public function setJobId(string $jobId): void;

    /**
     * Set target.
     *
     * @param \EonX\EasyAsync\Interfaces\TargetInterface $target
     *
     * @return void
     */
    public function setTarget(TargetInterface $target): void;

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void;
}
