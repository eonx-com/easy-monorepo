<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Batch\Store;

use EonX\EasyAsync\Batch\Batch;
use EonX\EasyAsync\Batch\Store\DoctrineDbalBatchStore;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyAsync\Tests\AbstractStoreTestCase;

/**
 * @deprecated since 3.3, will be removed in 4.0. Use eonx-com/easy-batch instead.
 */
final class DoctrineDbalBatchStoreTest extends AbstractStoreTestCase
{
    public function testFindReturnsNull(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $store = new DoctrineDbalBatchStore($this->getBatchFactory(), $conn);

        self::assertNull($store->find('invalid'));
    }

    public function testStoreAndFind(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $store = new DoctrineDbalBatchStore($this->getBatchFactory(), $conn);
        $batchId = 'batch-id';

        $batch = new Batch();
        $batch->setId($batchId);
        $batch->setThrowable(new \Exception('for-coverage'));

        $store->store($batch);

        $foundBatch = $store->find($batchId);

        if ($foundBatch instanceof BatchInterface) {
            self::assertEquals($batchId, $foundBatch->getId());
        }
    }
}
