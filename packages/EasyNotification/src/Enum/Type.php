<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Enum;

enum Type: string
{
    case Flash = 'flash';

    case Push = 'push';

    case RealTime = 'real_time';

    case Slack = 'slack';
}
