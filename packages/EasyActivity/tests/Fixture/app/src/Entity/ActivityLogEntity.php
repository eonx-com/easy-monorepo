<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixture\App\Entity;

use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;

final readonly class ActivityLogEntity implements ActivitySubjectInterface
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

    public function getAllowedActivityProperties(): ?array
    {
        return $this->allowedProperties;
    }

    public function getDisallowedActivityProperties(): array
    {
        return [];
    }

    public function getNestedObjectAllowedActivityProperties(): array
    {
        return [];
    }
}
