<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Dispatcher;

use EonX\EasyActivity\Common\Entity\ActivityLogEntry;

interface AsyncDispatcherInterface
{
    public function dispatch(ActivityLogEntry $activityLogEntry): void;
}
