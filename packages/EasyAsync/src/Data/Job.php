<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Data;

use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\TargetInterface;

class Job extends AbstractEasyAsyncData implements JobInterface
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

    /**
     * Job constructor.
     *
     * @param \EonX\EasyAsync\Interfaces\TargetInterface $target
     * @param string $type
     * @param null|int $total
     */
    public function __construct(TargetInterface $target, string $type, ?int $total = null)
    {
        parent::__construct($target, $type);

        $this->total = $total ?? 1;
        $this->status = self::STATUS_SCHEDULED;
    }

    /**
     * Create job from given array.
     *
     * @param mixed[] $data
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     */
    public static function fromArray(array $data): JobInterface
    {
        $job = new static(new Target($data['target_id'], $data['target_type']), $data['type'], $data['total']);

        $job->setStatus($data['status']);
        $job->setId($data['id']);
        $job
            ->setFailed($data['failed'])
            ->setProcessed($data['processed'])
            ->setSucceeded($data['succeeded']);

        return $job;
    }

    /**
     * @inheritDoc
     */
    public function getFailed(): int
    {
        return $this->failed;
    }

    /**
     * @inheritDoc
     */
    public function getProcessed(): int
    {
        return $this->processed;
    }

    /**
     * @inheritDoc
     */
    public function getSucceeded(): int
    {
        return $this->succeeded;
    }

    /**
     * @inheritDoc
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Set count of failed job logs.
     *
     * @param int $failed
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     */
    public function setFailed(int $failed): JobInterface
    {
        $this->failed = $failed;

        return $this;
    }

    /**
     * Set count of processed job logs.
     *
     * @param int $processed
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     */
    public function setProcessed(int $processed): JobInterface
    {
        $this->processed = $processed;

        return $this;
    }

    /**
     * Set count of succeeded job logs.
     *
     * @param int $succeeded
     *
     * @return \EonX\EasyAsync\Interfaces\JobInterface
     */
    public function setSucceeded(int $succeeded): JobInterface
    {
        $this->succeeded = $succeeded;

        return $this;
    }

    /**
     * Get array representation.
     *
     * @return mixed[]
     */
    public function toArray(): array
    {
        $array = [
            'failed' => $this->getFailed(),
            'processed' => $this->getProcessed(),
            'succeeded' => $this->getSucceeded(),
            'total' => $this->getTotal()
        ];

        return parent::toArray() + $array;
    }
}
