<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface JobFactoryInterface
{
    public function create(TargetInterface $target, string $type, ?int $total = null): JobInterface;
}
