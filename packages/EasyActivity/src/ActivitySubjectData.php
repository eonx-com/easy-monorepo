<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Interfaces\ActivitySubjectDataInterface;

final class ActivitySubjectData implements ActivitySubjectDataInterface
{
    public function __construct(
        private ?string $data = null,
        private ?string $oldData = null,
    ) {
    }

    public function getSubjectData(): ?string
    {
        return $this->data;
    }

    public function getSubjectOldData(): ?string
    {
        return $this->oldData;
    }
}
