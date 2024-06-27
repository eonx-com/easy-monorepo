<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Factory;

use EonX\EasyBatch\Common\ValueObject\BatchObjectInterface;

interface BatchObjectFactoryInterface
{
    public function createFromArray(array $data): BatchObjectInterface;
}
