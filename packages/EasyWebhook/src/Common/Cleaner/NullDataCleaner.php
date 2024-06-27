<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Cleaner;

final class NullDataCleaner implements DataCleanerInterface
{
    public function cleanUpData(array $data): array
    {
        return $data;
    }
}
