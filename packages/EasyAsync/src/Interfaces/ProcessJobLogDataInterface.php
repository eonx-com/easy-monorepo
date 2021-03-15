<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
interface ProcessJobLogDataInterface
{
    public function getJobId(): string;

    public function getTarget(): TargetInterface;

    public function getType(): string;
}
