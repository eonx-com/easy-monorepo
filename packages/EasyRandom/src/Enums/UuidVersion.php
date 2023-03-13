<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Enums;

enum UuidVersion: int
{
    case V4 = 4;
    case V6 = 6;
}
