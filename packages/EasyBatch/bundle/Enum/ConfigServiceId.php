<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Bundle\Enum;

enum ConfigServiceId: string
{
    case BatchIdStrategy = 'easy_batch.batch.id_strategy';

    case BatchItemIdStrategy = 'easy_batch.batch_item.id_strategy';

    case BatchItemTransformer = 'easy_batch.batch_item.transformer';

    case BatchMessageSerializer = 'easy_batch.batch.message_serializer';

    case BatchTransformer = 'easy_batch.batch.transformer';
}
