<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Interfaces\SubjectDataInterface;

final class SubjectData implements SubjectDataInterface
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

    public function getData(): ?string
    {
        return $this->data;
    }

    public function getOldData(): ?string
    {
        return $this->oldData;
    }
}
