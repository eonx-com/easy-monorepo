<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bridge;

use EonX\EasyAsync\Data\ProcessJobLogData;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Interfaces\ProcessJobLogDataInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;

trait WithProcessJobLogDataTrait
{
    /**
     * @var string
     */
    private $jobId;

    /**
     * @var mixed
     */
    private $targetId;

    /**
     * @var string
     */
    private $targetType;

    /**
     * @var string
     */
    private $type;

    /**
     * Get process job log data.
     *
     * @return \EonX\EasyAsync\Interfaces\ProcessJobLogDataInterface
     */
    public function getProcessJobLogData(): ProcessJobLogDataInterface
    {
        return new ProcessJobLogData($this->jobId, new Target($this->targetId, $this->targetType), $this->type);
    }

    /**
     * Set job id.
     *
     * @param string $jobId
     *
     * @return void
     */
    public function setJobId(string $jobId): void
    {
        $this->jobId = $jobId;
    }

    /**
     * Set target.
     *
     * @param \EonX\EasyAsync\Interfaces\TargetInterface $target
     *
     * @return void
     */
    public function setTarget(TargetInterface $target): void
    {
        $this->targetId = $target->getTargetId();
        $this->targetType = $target->getTargetType();
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
