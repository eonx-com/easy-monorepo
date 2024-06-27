<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Bundle\Enum;

enum ConfigParam: string
{
    case BatchClass = 'easy_batch.batch.class';

    case BatchItemClass = 'easy_batch.batch_item.class';

    case BatchItemPerPage = 'easy_batch.batch_item.per_page';

    case BatchItemTable = 'easy_batch.batch_item.table';

    case BatchTable = 'easy_batch.batch.table';

    case DateTimeFormat = 'easy_batch.date_time.format';

    case LockTtl = 'easy_batch.lock.ttl';
}
