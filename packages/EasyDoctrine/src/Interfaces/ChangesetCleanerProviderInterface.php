<?php

namespace EonX\EasyDoctrine\Interfaces;

interface ChangesetCleanerProviderInterface
{
    /**
     * @param class-string $oldClass
     * @param class-string $newClass
     *
     * @return \EonX\EasyDoctrine\Interfaces\ChangesetCleanerInterface<object>|null
     */
    public function getChangesetCleaner(string $oldClass, string $newClass): ?ChangesetCleanerInterface;
}
