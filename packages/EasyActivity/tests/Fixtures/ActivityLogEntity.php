<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixtures;

use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;

final class ActivityLogEntity implements ActivitySubjectInterface
{
    public function __construct(
        private string $id,
        private string $subjectType,
        private array $allowedProperties,
    ) {
    }

    public function getActivitySubjectId(): string
    {
        return $this->id;
    }

    public function getActivitySubjectType(): string
    {
        return $this->subjectType;
    }

    /**
     * @inheritDoc
     */
    public function getAllowedActivityProperties(): ?array
    {
        return $this->allowedProperties;
    }

    /**
     * @inheritDoc
     */
    public function getDisallowedActivityProperties(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getNestedObjectAllowedActivityProperties(): array
    {
        return [];
    }
}
