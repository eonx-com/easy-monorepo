<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface ProcessJobLogDataInterface
{
    /**
     * Get job id.
     *
     * @return string
     */
    public function getJobId(): string;

    /**
     * Get target.
     *
     * @return \EonX\EasyAsync\Interfaces\TargetInterface
     */
    public function getTarget(): TargetInterface;

    /**
     * Get type.
     *
     * @return string
     */
    public function getType(): string;
}
