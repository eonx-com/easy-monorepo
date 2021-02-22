<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Batch\Store;

use Carbon\Carbon;
use Doctrine\DBAL\Connection;
use EonX\EasyAsync\Interfaces\Batch\BatchItemInterface;
use EonX\EasyAsync\Interfaces\Batch\BatchItemStoreInterface;

final class DoctrineDbalBatchItemStore extends AbstractDoctrineDbalStore implements BatchItemStoreInterface
{
    public function __construct(Connection $conn, ?string $table = null)
    {
        parent::__construct($conn, $table ?? 'easy_async_batch_items');
    }

    public function store(BatchItemInterface $batchItem): BatchItemInterface
    {
        $exists = $this->existsInDb($batchItem->getId());
        $data = $batchItem->toArray();
        $now = Carbon::now('UTC');

        // Always set updated_at
        $data['updated_at'] = $now;

        // New batch item, insert
        if ($exists === false) {
            // Set created_at on new batch item only
            $data['created_at'] = $now;
            
            $this->conn->insert($this->table, $this->formatData($data));

            return $batchItem;
        }

        // Existing batch item, update
        $this->conn->update($this->table, $this->formatData($data), ['id' => $batchItem->getId()]);

        return $batchItem;
    }
}
