<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Data;

use EonX\EasyAsync\Interfaces\ProcessJobLogDataInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
final class ProcessJobLogData implements ProcessJobLogDataInterface
{
    /**
     * @var string
     */
    private $jobId;

    /**
     * @var \EonX\EasyAsync\Interfaces\TargetInterface
     */
    private $target;

    /**
     * @var string
     */
    private $type;

    public function __construct(string $jobId, TargetInterface $target, string $type)
    {
        $this->jobId = $jobId;
        $this->target = $target;
        $this->type = $type;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getTarget(): TargetInterface
    {
        return $this->target;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
