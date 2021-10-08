<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

use EonX\EasyActivity\ActivityLogEntry;

interface AsyncDispatcherInterface
{
    public function dispatch(ActivityLogEntry $activityLogEntry): void;
}
