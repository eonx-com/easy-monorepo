<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Factory;

use EonX\EasyBatch\Common\ValueObject\AbstractBatchObject;

interface BatchObjectFactoryInterface
{
    public function createFromArray(array $data): AbstractBatchObject;
}
