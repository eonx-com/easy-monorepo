<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

use EonX\EasyBatch\Interfaces\BatchObjectInterface;
use EonX\EasyBatch\Interfaces\BatchObjectRequiresApprovalInterface;

final class WithApprovalBatchObject implements BatchObjectRequiresApprovalInterface
{
    /**
     * @var \EonX\EasyBatch\Interfaces\BatchObjectInterface
     */
    private $batchObject;

    public function __construct(BatchObjectInterface $batchObject)
    {
        $this->batchObject = $batchObject;
    }

    public function getBatchObject(): BatchObjectInterface
    {
        return $this->batchObject;
    }
}
