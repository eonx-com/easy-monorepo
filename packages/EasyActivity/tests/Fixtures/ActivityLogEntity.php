<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixtures;

final class ActivityLogEntity implements ActivityLogEntityInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $property1;

    /**
     * @var string
     */
    private $property2;

    /**
     * @var string
     */
    private $property3;

    public function __construct(string $id, string $property1, string $property2, string $property3)
    {
        $this->id = $id;
        $this->property1 = $property1;
        $this->property2 = $property2;
        $this->property3 = $property3;
    }

    public function getActivityLoggableProperties(): array
    {
        return ['property1', 'property2'];
    }

    public function getActivitySubjectId(): string
    {
        return $this->id;
    }

    public function getActivitySubjectType(): string
    {
        return 'subject-type';
    }

    public function getProperty1(): string
    {
        return $this->property1;
    }

    public function getProperty2(): string
    {
        return $this->property2;
    }

    public function getProperty3(): string
    {
        return $this->property3;
    }
}
