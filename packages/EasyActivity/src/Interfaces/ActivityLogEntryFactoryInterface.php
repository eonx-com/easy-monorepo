<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

use EonX\EasyActivity\ActivityLogEntry;

interface ActivityLogEntryFactoryInterface
{
    public function create(string $action, object $object, array $changeSet): ?ActivityLogEntry;
}
