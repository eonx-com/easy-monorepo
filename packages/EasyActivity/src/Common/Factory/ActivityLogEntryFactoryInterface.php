<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Factory;

use EonX\EasyActivity\Common\Entity\ActivityLogEntry;

interface ActivityLogEntryFactoryInterface
{
    public function create(string $action, object $object, array $changeSet): ?ActivityLogEntry;
}
