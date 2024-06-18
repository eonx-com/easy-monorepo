<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Iterator;

use EonX\EasyBatch\Common\ValueObject\BatchItemIteratorConfig;

interface BatchItemIteratorInterface
{
    public function iterateThroughItems(BatchItemIteratorConfig $config): void;
}
