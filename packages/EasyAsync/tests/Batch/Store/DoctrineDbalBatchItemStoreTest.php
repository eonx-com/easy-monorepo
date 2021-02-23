<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Batch\Store;

use Carbon\Carbon;
use EonX\EasyAsync\Batch\BatchItem;
use EonX\EasyAsync\Batch\Store\DoctrineDbalBatchItemStore;
use EonX\EasyAsync\Tests\AbstractStoreTestCase;

final class DoctrineDbalBatchItemStoreTest extends AbstractStoreTestCase
{
    public function testStore(): void
    {
        $conn = $this->getDoctrineDbalConnection();
        $store = new DoctrineDbalBatchItemStore($conn);
        $now = Carbon::now();

        $batchItem = new BatchItem('batch-id', 'target-class', 'batch-item-id');
        $batchItem->setStartedAt($now);
        $batchItem->setFinishedAt($now);

        $store->store($batchItem);

        $result = $conn->fetchAssociative("select * from easy_async_batch_items where id = 'batch-item-id'");

        self::assertTrue(\is_array($result));
    }
}
