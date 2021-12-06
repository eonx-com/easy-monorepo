<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Interfaces;

use DateTimeImmutable;

interface TimestampableInterface
{
    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;

    public function updateTimestamps(): void;
}
