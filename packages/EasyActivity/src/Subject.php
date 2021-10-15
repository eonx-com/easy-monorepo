<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Interfaces\SubjectInterface;

final class Subject implements SubjectInterface
{
    /**
     * @var string|null
     */
    private $data;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string|null
     */
    private $oldData;

    /**
     * @var string
     */
    private $type;

    public function __construct(
        string $id,
        string $type,
        ?string $data = null,
        ?string $oldData = null
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->data = $data;
        $this->oldData = $oldData;
    }

    public function getSubjectData(): ?string
    {
        return $this->data;
    }

    public function getSubjectId(): string
    {
        return $this->id;
    }

    public function getSubjectOldData(): ?string
    {
        return $this->oldData;
    }

    public function getSubjectType(): string
    {
        return $this->type;
    }
}
