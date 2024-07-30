<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Common\Enum;

enum WebhookOption: string
{
    case Body = 'body';

    case BodyAsString = 'body_as_string';

    case CurrentAttempt = 'current_attempt';

    case Event = 'event';

    case HttpOptions = 'http_options';

    case Id = 'id';

    case MaxAttempt = 'max_attempt';

    case Method = 'method';

    case Secret = 'secret';

    case SendAfter = 'send_after';

    case SendNow = 'send_now';

    case Status = 'status';

    case Url = 'url';
}
