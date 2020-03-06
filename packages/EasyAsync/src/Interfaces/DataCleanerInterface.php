<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface DataCleanerInterface
{
    public function remove(JobInterface $job): void;
}
