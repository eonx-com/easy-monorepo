<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Store;

use EonX\EasyActivity\Common\Entity\ActivityLogEntry;

interface StoreInterface
{
    public function store(ActivityLogEntry $logEntry): ActivityLogEntry;
}
