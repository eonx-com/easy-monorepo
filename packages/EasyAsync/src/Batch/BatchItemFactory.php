<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch;

use EonX\EasyAsync\Interfaces\Batch\BatchItemFactoryInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemInterface;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

final class BatchItemFactory implements BatchItemFactoryInterface
{
    /**
     * @var \EonX\EasyRandom\Interfaces\RandomGeneratorInterface
     */
    private $random;

    public function __construct(RandomGeneratorInterface $random)
    {
        $this->random = $random;
    }

    public function create(string $batchId, string $targetClass, ?string $id = null): BatchItemInterface
    {
        return new BatchItem($batchId, $targetClass, $id ?? $this->random->uuidV4());
    }
}
