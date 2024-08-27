<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Logger;

use EonX\EasyActivity\Common\Enum\ActivityAction;

interface ActivityLoggerInterface
{
    public function addActivityLogEntry(ActivityAction|string $action, object $object, array $changeSet): void;
}
