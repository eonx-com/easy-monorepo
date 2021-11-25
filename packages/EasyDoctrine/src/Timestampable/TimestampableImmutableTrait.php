<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Timestampable;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;

trait TimestampableImmutableTrait
{
    /**
     * @var \DateTimeInterface
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface
     */
    protected $updatedAt;

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function updateTimestamps(): void
    {
        $dateTime = CarbonImmutable::now();

        if (isset($this->createdAt) === false) {
            $this->createdAt = $dateTime;
        }

        if ($this->createdAt instanceof DateTimeImmutable === false) {
            $this->createdAt = CarbonImmutable::createFromMutable($this->createdAt);
        }

        $this->updatedAt = $dateTime;
    }
}
