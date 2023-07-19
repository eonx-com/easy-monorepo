<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Interfaces;

/**
 * @template T of object
 */
interface ChangesetCleanerInterface
{
    /**
     * @param class-string $class
     */
    public function supports(string $class): bool;

    /**
     * @param T $oldValue
     * @param T $newValue
     */
    public function shouldBeCleared(object $oldValue, object $newValue): bool;
}
