<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Iterator;

use EonX\EasyBatch\Common\ValueObject\BatchItemIteratorConfigInterface;

interface BatchItemIteratorInterface
{
    public function iterateThroughItems(BatchItemIteratorConfigInterface $config): void;
}
