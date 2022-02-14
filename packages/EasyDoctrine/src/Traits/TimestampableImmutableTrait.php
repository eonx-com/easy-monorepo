<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Traits;

use Carbon\CarbonImmutable;
use DateTimeImmutable;

trait TimestampableImmutableTrait
{
    /**
     * @var \DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @var \DateTimeImmutable
     */
    protected $updatedAt;

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateTimestamps(): void
    {
        $dateTime = CarbonImmutable::now();

        if ($this->createdAt === null) {
            $this->createdAt = $dateTime;
        }

        $this->updatedAt = $dateTime;
    }
}
