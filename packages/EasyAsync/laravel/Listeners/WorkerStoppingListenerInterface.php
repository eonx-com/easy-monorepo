<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Laravel\Listeners;

interface WorkerStoppingListenerInterface
{
    public const REASONS = [
        1 => 'Timeout',
        12 => 'Memory exceeded',
    ];
}
