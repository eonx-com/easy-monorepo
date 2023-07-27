<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyWebhook\Interfaces\Stores\DataCleanerInterface;

final class NullDataCleaner implements DataCleanerInterface
{
    public function cleanUpData(array $data): array
    {
        return $data;
    }
}
