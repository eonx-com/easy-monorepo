<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface SubjectInterface
{
    public function getSubjectData(): ?string;

    public function getSubjectId(): string;

    public function getSubjectOldData(): ?string;

    public function getSubjectType(): string;
}
