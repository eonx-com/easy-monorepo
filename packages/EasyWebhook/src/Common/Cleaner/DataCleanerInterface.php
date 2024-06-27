<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Cleaner;

interface DataCleanerInterface
{
    public function cleanUpData(array $data): array;
}
