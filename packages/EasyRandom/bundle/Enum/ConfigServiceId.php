<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Bundle\Enum;

enum ConfigServiceId: string
{
    case UuidFactory = 'easy_random.uuid_factory';
}
