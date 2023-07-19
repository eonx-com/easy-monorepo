<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Providers;

use EonX\EasyDoctrine\Interfaces\ChangesetCleanerInterface;
use EonX\EasyDoctrine\Interfaces\ChangesetCleanerProviderInterface;

final class ChangesetCleanerProvider implements ChangesetCleanerProviderInterface
{
    /**
     * @var iterable<\EonX\EasyDoctrine\Interfaces\ChangesetCleanerInterface<object>>
     */
    private iterable $cleaners;

    /**
     * @var array<class-string, \EonX\EasyDoctrine\Interfaces\ChangesetCleanerInterface<object>|null>
     */
    private array $cleanersByClass = [];

    /**
     * @param iterable<\EonX\EasyDoctrine\Interfaces\ChangesetCleanerInterface<T>> $cleaners
     *
     * @template T of object
     */
    public function __construct(array $cleaners)
    {
        $this->cleaners = $cleaners;
    }

    public function getChangesetCleaner(string $oldClass, string $newClass): ?ChangesetCleanerInterface
    {
        if (isset($this->cleanersByClass[$oldClass], $this->cleanersByClass[$newClass])) {
            return $this->cleanersByClass[$oldClass];
        }

        $cleanerForClass = null;
        foreach ($this->cleaners as $cleaner) {
            if ($cleaner->supports($oldClass) && $cleaner->supports($newClass)) {
                $cleanerForClass = $cleaner;
                break;
            }
        }

        $this->cleanersByClass[$oldClass] = $cleanerForClass;
        $this->cleanersByClass[$newClass] = $cleanerForClass;

        return $cleanerForClass;
    }
}
