<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Logger;

interface ActivityLoggerInterface
{
    public function addActivityLogEntry(string $action, object $object, array $changeSet): void;
}
