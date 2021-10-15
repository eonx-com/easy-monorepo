<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Stubs;

use EonX\EasyActivity\ActivityLogEntry;
use EonX\EasyActivity\Interfaces\StoreInterface;

final class ActivityLogStoreStub implements StoreInterface
{
    public function getIdentifier(object $subject): string
    {
        if (\method_exists($subject, 'getId') === false) {
            throw new \RuntimeException('getId() not defined');
        }

        return (string)$subject->getId();
    }

    public function store(ActivityLogEntry $logEntry): ActivityLogEntry
    {
        return $logEntry;
    }
}
