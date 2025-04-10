<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Entity;

interface ActivitySubjectInterface
{
    public function getActivitySubjectId(): string;

    public function getActivitySubjectType(): string;

    public function getAllowedActivityActions(): array;

    /**
     * @return list<string>|array<string, list<string>>|null
     */
    public function getAllowedActivityProperties(): ?array;

    /**
     * @return list<string>
     */
    public function getDisallowedActivityProperties(): array;

    public function getFullySerializableActivityProperties(): array;

    /**
     * @return array<string, list<string>>
     */
    public function getNestedObjectAllowedActivityProperties(): array;
}
