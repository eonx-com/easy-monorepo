<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

use EonX\EasyActivity\ActivityLogEntry;

interface ActivityLogEntryFactoryInterface
{
    /**
     * @param array<string, mixed> $changeSet
     */
    public function create(string $action, object $object, array $changeSet): ?ActivityLogEntry;
}
