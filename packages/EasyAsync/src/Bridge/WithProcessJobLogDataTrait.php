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

    public function getProcessJobLogData(): ProcessJobLogDataInterface
    {
        return new ProcessJobLogData($this->jobId, new Target($this->targetId, $this->targetType), $this->type);
    }

    public function setJobId(string $jobId): void
    {
        $this->jobId = $jobId;
    }

    public function setTarget(TargetInterface $target): void
    {
        $this->targetId = $target->getTargetId();
        $this->targetType = $target->getTargetType();
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
