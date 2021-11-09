<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Interfaces\ActivitySubjectInterface;

final class ActivitySubject implements ActivitySubjectInterface
{
    /**
     * @var array<string|array<string, mixed>>
     */
    private $allowedProperties;

    /**
     * @var array<string|array<string, mixed>>
     */
    private $disallowedProperties;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @param array<string|array<string, mixed>> $allowedProperties
     * @param array<string|array<string, mixed>> $disallowedProperties
     */
    public function __construct(
        string $id,
        string $type,
        array $allowedProperties,
        array $disallowedProperties
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->allowedProperties = $allowedProperties;
        $this->disallowedProperties = $disallowedProperties;
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
    public function getAllowedActivityProperties(): array
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
}
