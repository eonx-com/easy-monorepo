<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixtures;

use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;

final class ActivityLogEntity implements ActivitySubjectInterface
{
    /**
     * @var array<string>|array<string, mixed>
     */
    private $allowedProperties;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $subjectType;

    /**
     * @param array<string>|array<string, mixed> $allowedProperties
     */
    public function __construct(string $id, string $subjectType, array $allowedProperties)
    {
        $this->id = $id;
        $this->subjectType = $subjectType;
        $this->allowedProperties = $allowedProperties;
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
        return $this->allowedProperties;
    }

    public function getDisallowedActivityProperties(): array
    {
        return [];
    }

    public function getNestedObjectAllowedProperties(): array
    {
        return [];
    }
}
