<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface SubjectResolverInterface
{
    /**
     * @param string $action
     * @param object $object
     * @param array<string, mixed> $changeSet
     */
    public function resolveSubject(string $action, object $object, array $changeSet): ?SubjectInterface;
}
