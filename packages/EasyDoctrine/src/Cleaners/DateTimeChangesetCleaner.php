<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Cleaners;

use DateTimeInterface;
use EonX\EasyDoctrine\Interfaces\ChangesetCleanerInterface;

/**
 * @implements \EonX\EasyDoctrine\Interfaces\ChangesetCleanerInterface<\DateTimeInterface>
 */
final class DateTimeChangesetCleaner implements ChangesetCleanerInterface
{
    /**
     * @var string
     */
    private const DATETIME_COMPARISON_FORMAT = 'Y-m-d H:i:s.uP';

    public function supports(string $class): bool
    {
        return \is_subclass_of($class, DateTimeInterface::class);
    }

    public function shouldBeCleared(object $oldValue, object $newValue): bool
    {
        return $oldValue->format(self::DATETIME_COMPARISON_FORMAT) ===
            $newValue->format(self::DATETIME_COMPARISON_FORMAT);
    }
}
