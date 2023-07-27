<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface ActivityLoggerInterface
{
    public function addActivityLogEntry(string $action, object $object, array $changeSet): void;
}
