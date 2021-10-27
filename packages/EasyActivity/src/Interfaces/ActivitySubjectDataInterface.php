<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface ActivitySubjectDataInterface
{
    public function getSubjectData(): ?string;

    public function getSubjectOldData(): ?string;
}
