<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Enum;

enum MiddlewarePriority: int
{
    case CoreAfter = 5000;

    case CoreBefore = -5000;
}
