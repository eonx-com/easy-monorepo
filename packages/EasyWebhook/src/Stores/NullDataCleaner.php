<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Stores;

use EonX\EasyWebhook\Interfaces\Stores\DataCleanerInterface;

final class NullDataCleaner implements DataCleanerInterface
{
    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function cleanUpData(array $data): array
    {
        return $data;
    }
}
