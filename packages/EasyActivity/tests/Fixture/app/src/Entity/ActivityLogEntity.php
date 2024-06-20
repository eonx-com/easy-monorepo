<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixture\App\Entity;

use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;

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
     * @inheritdoc
     */
    public function getAllowedActivityProperties(): ?array
    {
        return $this->allowedProperties;
    }

    /**
     * @inheritdoc
     */
    public function getDisallowedActivityProperties(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getNestedObjectAllowedActivityProperties(): array
    {
        return [];
    }
}
