<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

use EonX\EasyBatch\Interfaces\BatchObjectRequiresApprovalInterface;

final class WithApprovalBatchObject extends AbstractBatchObjectDecorator implements BatchObjectRequiresApprovalInterface
{
    // No body needed.
}
