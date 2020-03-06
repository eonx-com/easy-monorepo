<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface JobLogFactoryInterface
{
    public function create(TargetInterface $target, string $type, string $jobId): JobLogInterface;
}
