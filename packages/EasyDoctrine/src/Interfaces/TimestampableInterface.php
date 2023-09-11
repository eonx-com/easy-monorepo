<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Interfaces;

use Carbon\CarbonImmutable;

interface TimestampableInterface
{
    public function getCreatedAt(): CarbonImmutable;

    public function getUpdatedAt(): CarbonImmutable;

    public function updateTimestamps(): void;
}
