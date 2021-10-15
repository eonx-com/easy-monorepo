<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface SubjectInterface
{
    /**
     * @return string[]|null
     */
    public function getSubjectAllowedProperties(): ?array;

    /**
     * @return string[]|null
     */
    public function getSubjectDisallowedProperties(): ?array;

    public function getSubjectId(): string;

    public function getSubjectType(): string;

    public function isSubjectEnabled(): bool;
}
