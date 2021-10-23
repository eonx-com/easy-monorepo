<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Interfaces\ActivitySubjectDataInterface;

final class ActivitySubjectData implements ActivitySubjectDataInterface
{
    /**
     * @var string|null
     */
    private $data;

    /**
     * @var string|null
     */
    private $oldData;

    public function __construct(?string $data, ?string $oldData)
    {
        $this->data = $data;
        $this->oldData = $oldData;
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
