<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces;

use EonX\EasyPagination\Interfaces\LengthAwarePaginatorInterface;
use EonX\EasyPagination\Interfaces\StartSizeDataInterface;

interface SendAfterAwareWebhookResultStoreInterface
{
    public function findDueWebhooks(
        StartSizeDataInterface $data,
        ?\DateTimeInterface $sendAfter = null,
        ?string $timezone = null
    ): LengthAwarePaginatorInterface;
}
