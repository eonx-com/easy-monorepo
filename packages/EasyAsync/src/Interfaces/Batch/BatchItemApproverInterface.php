<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces\Batch;

interface BatchItemApproverInterface
{
    public function approve(BatchItemInterface $batchItem): BatchItemInterface;
}
