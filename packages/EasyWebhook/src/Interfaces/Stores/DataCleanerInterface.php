<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces\Stores;

interface DataCleanerInterface
{
    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function cleanUpData(array $data): array;
}
