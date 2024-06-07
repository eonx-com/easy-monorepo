<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Bundle\Enum;

enum ConfigParam: string
{
    case DoctrinePersistentConnectionsMaxIdleTime = 'easy_async.doctrine_persistent_connections_max_idle_time';

    case LogChannel = 'async';

    case MessengerMiddlewareAutoRegister = 'easy_async.messenger_middleware_auto_register';

    case MessengerWorkerStopMaxMessages = 'easy_async.messenger_worker_stop_max_messages';

    case MessengerWorkerStopMaxTime = 'easy_async.messenger_worker_stop_max_time';

    case MessengerWorkerStopMinMessages = 'easy_async.messenger_worker_stop_min_messages';

    case MessengerWorkerStopMinTime = 'easy_async.messenger_worker_stop_min_time';
}
