<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;

final class ActivitySubject implements ActivitySubjectInterface
{
    /**
     * @var list<string>|array<string, list<string>>|null
     */
    private $allowedProperties;

    /**
     * @var list<string>
     */
    private $disallowedProperties;

    /**
     * @var string
     */
    private $id;

    /**
     * @var array<string, list<string>>
     */
    private $nestedObjectAllowedProperties;

    /**
     * @var string
     */
    private $type;

    /**
     * @param list<string>|array<string, list<string>> $allowedProperties
     * @param list<string> $disallowedProperties
     * @param array<string, list<string>> $nestedObjectAllowedProperties
     */
    public function __construct(
        string $id,
        string $type,
        ?array $allowedProperties,
        array $disallowedProperties,
        array $nestedObjectAllowedProperties
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->allowedProperties = $allowedProperties;
        $this->disallowedProperties = $disallowedProperties;
        $this->nestedObjectAllowedProperties = $nestedObjectAllowedProperties;
    }

    public function getActivitySubjectId(): string
    {
        return $this->id;
    }

    public function getActivitySubjectType(): string
    {
        return $this->type;
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
        return $this->disallowedProperties;
    }

    /**
     * @inheritDoc
     */
    public function getNestedObjectAllowedActivityProperties(): array
    {
        return $this->nestedObjectAllowedProperties;
    }
}
