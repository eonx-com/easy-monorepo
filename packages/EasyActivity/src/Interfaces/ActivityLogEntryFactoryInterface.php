<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

use EonX\EasyActivity\ActivityLogEntry;

interface ActivityLogEntryFactoryInterface
{
    /**
     * @param string $action
     * @param object $object
     * @param array<string, mixed> $changeSet
     *
     * @return ActivityLogEntry
     */
    public function create(
        string $action,
        object $object,
        array $changeSet
    ): ?ActivityLogEntry;
}
