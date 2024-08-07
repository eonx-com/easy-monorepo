<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Enum;

enum BatchItemType: string
{
    case Message = 'message';

    case NestedBatch = 'nested_batch';
}
