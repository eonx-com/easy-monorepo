<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface ProcessJobLogDataInterface
{
    public function getJobId(): string;

    public function getTarget(): TargetInterface;

    public function getType(): string;
}
