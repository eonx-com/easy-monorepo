<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces\Stores;

interface DataCleanerInterface
{
    public function cleanUpData(array $data): array;
}
