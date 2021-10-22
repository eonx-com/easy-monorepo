<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface SubjectDataResolverInterface
{
    /**
     * @param array<string, mixed> $changeSet
     */
    public function resolve(
        string $action,
        ActivitySubjectInterface $subject,
        array $changeSet
    ): ?ActivitySubjectDataInterface;
}
