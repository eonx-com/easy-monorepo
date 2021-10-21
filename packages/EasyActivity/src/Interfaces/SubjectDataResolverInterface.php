<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface SubjectDataResolverInterface
{
    /**
     * @param array<string, mixed> $changeSet
     */
    public function resolveSubjectData(
        string $action,
        ActivitySubjectInterface $subject,
        array $changeSet
    ): ?SubjectDataInterface;
}
