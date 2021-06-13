<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

use EonX\EasyBatch\Interfaces\BatchObjectRequiresApprovalInterface;

final class WithApprovalObject extends AbstractObjectDecorator implements BatchObjectRequiresApprovalInterface
{
    // No body needed.
}
