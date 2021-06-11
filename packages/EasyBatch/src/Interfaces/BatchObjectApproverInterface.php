<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchObjectApproverInterface
{
    public function approve(BatchObjectInterface $batchObject): BatchObjectInterface;
}
