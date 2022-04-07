<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Traits;

use Carbon\CarbonImmutable;

trait TimestampableTrait
{
    protected CarbonImmutable $createdAt;

    protected CarbonImmutable $updatedAt;

    public function getCreatedAt(): CarbonImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): CarbonImmutable
    {
        return $this->updatedAt;
    }

    public function updateTimestamps(): void
    {
        $dateTime = CarbonImmutable::now();

        if (isset($this->createdAt) === false) {
            $this->createdAt = $dateTime;
        }

        $this->updatedAt = $dateTime;
    }
}
