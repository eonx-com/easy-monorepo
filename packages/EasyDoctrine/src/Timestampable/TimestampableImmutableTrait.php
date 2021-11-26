<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Timestampable;

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

        if (isset($this->createdAt) === false) {
            $this->createdAt = $dateTime;
        }

        $this->updatedAt = $dateTime;
    }
}
