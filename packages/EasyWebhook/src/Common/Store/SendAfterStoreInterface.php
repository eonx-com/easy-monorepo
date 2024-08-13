<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Store;

use DateTimeInterface;
use EonX\EasyPagination\Pagination\PaginationInterface;
use EonX\EasyPagination\Paginator\LengthAwarePaginatorInterface;

interface SendAfterStoreInterface extends StoreInterface
{
    public function findDueWebhooks(
        PaginationInterface $pagination,
        ?DateTimeInterface $sendAfter = null,
        ?string $timezone = null,
    ): LengthAwarePaginatorInterface;
}
