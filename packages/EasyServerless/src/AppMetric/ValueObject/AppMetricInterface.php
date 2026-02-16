<?php
declare(strict_types=1);

namespace EonX\EasyServerless\AppMetric\ValueObject;

interface AppMetricInterface
{
    public function getDimensions(): array;

    public function getName(): string;

    public function getNamespace(): ?string;
}
