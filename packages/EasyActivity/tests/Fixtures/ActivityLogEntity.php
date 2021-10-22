<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixtures;

use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;

final class ActivityLogEntity implements ActivitySubjectInterface
{
    /**
     * @var array<string>|array<string, mixed>
     */
    private $allowedPropeties;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $subjectType;

    /**
     * @param array<string>|array<string, mixed> $allowedPropeties
     */
    public function __construct(string $id, string $subjectType, array $allowedPropeties)
    {
        $this->id = $id;
        $this->subjectType = $subjectType;
        $this->allowedPropeties = $allowedPropeties;
    }

    public function getActivitySubjectId(): string
    {
        return $this->id;
    }

    public function getActivitySubjectType(): string
    {
        return $this->subjectType;
    }

    public function getAllowedActivityProperties(): array
    {
        return $this->allowedPropeties;
    }

    public function getDisallowedActivityProperties(): ?array
    {
        return null;
    }
}
