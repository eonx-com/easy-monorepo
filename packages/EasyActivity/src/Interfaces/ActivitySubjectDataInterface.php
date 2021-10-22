<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Interfaces;

interface ActivitySubjectDataInterface
{
    public function getData(): ?string;

    public function getOldData(): ?string;
}
