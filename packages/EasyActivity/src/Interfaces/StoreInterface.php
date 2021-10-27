<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

use EonX\EasyActivity\ActivityLogEntry;

interface StoreInterface
{
    public function store(ActivityLogEntry $logEntry): ActivityLogEntry;
}
