<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Stores;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\StoreInterface;

final class NullStore implements StoreInterface
{
    public function getIdentifier(object $subject): ?string
    {
        return null;
    }

    public function store(ActivityLogEntry $logEntry): ActivityLogEntry
    {
        return $logEntry;
    }
}
