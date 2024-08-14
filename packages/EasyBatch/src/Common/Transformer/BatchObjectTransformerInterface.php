<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Transformer;

use EonX\EasyBatch\Common\ValueObject\AbstractBatchObject;

interface BatchObjectTransformerInterface
{
    public function instantiateForClass(?string $class = null): AbstractBatchObject;

    public function transformToArray(AbstractBatchObject $batchObject): array;

    public function transformToObject(array $data): AbstractBatchObject;
}
