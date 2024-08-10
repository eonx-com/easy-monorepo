<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\ValueObject;

final readonly class ActivitySubjectData
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
