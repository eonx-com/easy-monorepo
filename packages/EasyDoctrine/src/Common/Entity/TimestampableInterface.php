<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Entity;

use Carbon\CarbonImmutable;

interface TimestampableInterface
{
    public function getCreatedAt(): CarbonImmutable;

    public function getUpdatedAt(): CarbonImmutable;

    public function updateTimestamps(?CarbonImmutable $dateTime = null): void;
}
