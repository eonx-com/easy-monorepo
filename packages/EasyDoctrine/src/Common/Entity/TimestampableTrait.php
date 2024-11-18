<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Entity;

use Carbon\CarbonImmutable;

trait TimestampableTrait
{
    protected CarbonImmutable $createdAt;

    protected CarbonImmutable $updatedAt;

    private bool $isSetUpdatedAt = false;

    public function getCreatedAt(): CarbonImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): CarbonImmutable
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(CarbonImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setUpdatedAt(CarbonImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        $this->isSetUpdatedAt = true;

        return $this;
    }

    public function updateTimestamps(): void
    {
        $dateTime = CarbonImmutable::now();

        if (isset($this->createdAt) === false) {
            $this->createdAt = $dateTime;
        }

        if ($this->isSetUpdatedAt === false) {
            $this->updatedAt = $dateTime;
        }
    }
}
