<?php

declare(strict_types=1);

namespace EonX\EasyCore\DoctrineBehaviors;

use DateTimeImmutable;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

trait TimestampableImmutableTrait
{
    use TimestampableTrait {
        updateTimestamps as protected updateMutableTimestamps;
    }

    /**
     * Updates createdAt and updatedAt timestamps.
     */
    public function updateTimestamps(): void
    {
        $this->updateMutableTimestamps();

        if ($this->createdAt instanceof DateTimeImmutable === false) {
            $this->createdAt = DateTimeImmutable::createFromMutable($this->createdAt);
        }

        /* @phpstan-ignore-next-line */
        $this->updatedAt = DateTimeImmutable::createFromMutable($this->updatedAt);
    }
}
