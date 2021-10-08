<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

use EonX\EasyActivity\ActivityLogEntry;

interface StoreInterface
{
    public function getIdentifier(object $subject): ?string;

    public function store(ActivityLogEntry $logEntry): ActivityLogEntry;
}
