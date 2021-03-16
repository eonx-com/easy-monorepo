<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
interface JobFactoryInterface
{
    public function create(TargetInterface $target, string $type, ?int $total = null): JobInterface;
}
