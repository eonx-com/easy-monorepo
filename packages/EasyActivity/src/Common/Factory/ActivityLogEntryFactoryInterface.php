<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Factory;

use EonX\EasyActivity\Common\Entity\ActivityLogEntry;
use EonX\EasyActivity\Common\Enum\ActivityAction;

interface ActivityLogEntryFactoryInterface
{
    public function create(ActivityAction|string $action, object $object, array $changeSet): ?ActivityLogEntry;
}
