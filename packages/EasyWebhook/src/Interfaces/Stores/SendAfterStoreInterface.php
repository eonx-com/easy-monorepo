<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Interfaces\Stores;

use DateTimeInterface;
use EonX\EasyPagination\Paginator\LengthAwarePaginatorInterface;
use EonX\EasyPagination\ValueObject\PaginationInterface;

interface SendAfterStoreInterface extends StoreInterface
{
    public function findDueWebhooks(
        PaginationInterface $pagination,
        ?DateTimeInterface $sendAfter = null,
        ?string $timezone = null,
    ): LengthAwarePaginatorInterface;
}
