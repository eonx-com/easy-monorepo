<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces\Stores;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

interface SendAfterStoreInterface extends StoreInterface
{
    public function findDueWebhooks(
        StartSizeDataInterface $startSize,
        ?\DateTimeInterface $sendAfter = null,
        ?string $timezone = null
    ): LengthAwarePaginatorInterface;
}
