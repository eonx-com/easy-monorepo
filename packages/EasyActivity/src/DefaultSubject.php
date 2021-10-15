<?php

declare(strict_types=1);

namespace EonX\EasyActivity;

use EonX\EasyActivity\Interfaces\SubjectInterface;

final class DefaultSubject implements SubjectInterface
{
    /**
     * @var string[]|null
     */
    private $allowedProperties;

    /**
     * @var string[]|null
     */
    private $disallowedProperties;

    /**
     * @var bool
     */
    private $subjectEnabled;

    /**
     * @var string
     */
    private $subjectId;

    /**
     * @var string
     */
    private $subjectType;

    public function __construct(
        string $subjectId,
        string $subjectType,
        bool $subjectEnabled = true,
        ?array $allowedProperties = null,
        ?array $disallowedProperties = null
    ) {
        $this->subjectId = $subjectId;
        $this->subjectType = $subjectType;
        $this->subjectEnabled = $subjectEnabled;
        $this->allowedProperties = $allowedProperties;
        $this->disallowedProperties = $disallowedProperties;
    }

    public function getSubjectAllowedProperties(): ?array
    {
        return $this->allowedProperties;
    }

    public function getSubjectDisallowedProperties(): ?array
    {
        return $this->disallowedProperties;
    }

    public function getSubjectId(): string
    {
        return $this->subjectId;
    }

    public function getSubjectType(): string
    {
        return $this->subjectType;
    }

    public function isSubjectEnabled(): bool
    {
        return $this->subjectEnabled;
    }
}
