<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

use EonX\EasyBatch\Interfaces\BatchItemInterface;
use EonX\EasyBatch\Interfaces\BatchItemWithClassInterface;

class WithClassBatchItem extends AbstractBatchObjectDecorator implements BatchItemWithClassInterface
{
    /**
     * @var string
     */
    private $class;

    public function __construct(BatchItemInterface $batchItem, string $class)
    {
        $this->class = $class;

        parent::__construct($batchItem);
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
