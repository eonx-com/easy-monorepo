<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface ActivitySubjectInterface
{
    public function getActivitySubjectId(): string;

    public function getActivitySubjectType(): string;

    /**
     * @return array<string>|array<string, mixed>
     */
    public function getAllowedActivityProperties(): ?array;

    /**
     * @return array<string>|array<string, mixed>
     */
    public function getDisallowedActivityProperties(): ?array;
}
