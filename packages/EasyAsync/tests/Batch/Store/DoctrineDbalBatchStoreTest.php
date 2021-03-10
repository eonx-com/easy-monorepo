<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Batch\Store;

use EonX\EasyAsync\Batch\Batch;
use EonX\EasyAsync\Batch\Store\DoctrineDbalBatchStore;
use EonX\EasyAsync\Interfaces\Batch\BatchInterface;
use EonX\EasyAsync\Tests\AbstractStoreTestCase;

final class DoctrineDbalBatchStoreTest extends AbstractStoreTestCase
{
    public function testFindReturnsNull(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $store = new DoctrineDbalBatchStore($conn);

        self::assertNull($store->find('invalid'));
    }

    public function testStoreAndFind(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $store = new DoctrineDbalBatchStore($conn);
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
