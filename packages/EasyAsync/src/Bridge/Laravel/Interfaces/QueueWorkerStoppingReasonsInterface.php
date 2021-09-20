<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Bridge\Laravel\Interfaces;

interface QueueWorkerStoppingReasonsInterface
{
    /**
     * @var string[]
     */
    public const REASONS = [
        1 => 'Timeout',
        12 => 'Memory exceeded',
    ];
}
