<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface ActivitySubjectInterface
{
    /**
     * @return array<string>|array<string, mixed>
     */
    public function getActivityAllowedProperties(): ?array;

    /**
     * @return array<string>|array<string, mixed>
     */
    public function getActivityDisallowedProperties(): ?array;

    public function getActivitySubjectId(): string;

    public function getActivitySubjectType(): string;
}
