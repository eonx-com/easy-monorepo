<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface ActivityLoggerInterface
{
    /**
     * @param array<string, mixed> $changeSet
     */
    public function addActivityLogEntry(string $action, object $object, array $changeSet): void;
}
