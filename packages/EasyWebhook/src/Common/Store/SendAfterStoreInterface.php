<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Store;

use DateTimeInterface;
use EonX\EasyPagination\Paginator\LengthAwarePaginatorInterface;
use EonX\EasyPagination\ValueObject\Pagination;

interface SendAfterStoreInterface extends StoreInterface
{
    public function findDueWebhooks(
        Pagination $pagination,
        ?DateTimeInterface $sendAfter = null,
        ?string $timezone = null,
    ): LengthAwarePaginatorInterface;
}
