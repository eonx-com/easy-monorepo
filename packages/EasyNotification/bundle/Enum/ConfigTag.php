<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Bundle\Enum;

enum ConfigTag: string
{
    case QueueMessageConfigurator = 'easy_notification.queue_message_configurator';
}
