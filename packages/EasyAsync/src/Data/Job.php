<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Data;

use EonX\EasyAsync\Helpers\PropertyHelper;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;

final class Job extends AbstractEasyAsyncData implements JobInterface
{
    /**
     * @var int
     */
    protected $failed = 0;

    /**
     * @var int
     */
    protected $processed = 0;

    /**
     * @var int
     */
    protected $succeeded = 0;

    /**
     * @var int
     */
    protected $total;

    public function __construct(TargetInterface $target, string $type, ?int $total = null)
    {
        parent::__construct($target, $type);

        $this->total = $total ?? 1;
        $this->status = self::STATUS_SCHEDULED;
    }

    /**
     * @param mixed[] $data
     */
    public static function fromArray(array $data): JobInterface
    {
        $job = new self(new Target($data['target_id'], $data['target_type']), $data['type'], (int)$data['total']);

        $job->setStatus($data['status']);
        $job->setId($data['id']);

        PropertyHelper::setIntProperties($job, $data, ['failed', 'processed', 'succeeded']);

        return $job;
    }

    public function getFailed(): int
    {
        return $this->failed;
    }

    public function getProcessed(): int
    {
        return $this->processed;
    }

    public function getSucceeded(): int
    {
        return $this->succeeded;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setFailed(int $failed): JobInterface
    {
        $this->failed = $failed;

        return $this;
    }

    public function setProcessed(int $processed): JobInterface
    {
        $this->processed = $processed;

        return $this;
    }

    public function setSucceeded(int $succeeded): JobInterface
    {
        $this->succeeded = $succeeded;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        $array = [
            'failed' => $this->getFailed(),
            'processed' => $this->getProcessed(),
            'succeeded' => $this->getSucceeded(),
            'total' => $this->getTotal(),
        ];

        return parent::toArray() + $array;
    }
}
