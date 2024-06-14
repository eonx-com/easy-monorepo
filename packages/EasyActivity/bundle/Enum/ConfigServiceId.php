<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Bundle\Enum;

enum ConfigServiceId: string
{
    case CircularReferenceHandler = 'easy_activity.circular_reference_handler';

    case Serializer = 'easy_activity.serializer';
}
