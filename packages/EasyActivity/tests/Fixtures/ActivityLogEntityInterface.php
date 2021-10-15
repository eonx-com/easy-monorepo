<?php

declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Fixtures;

interface ActivityLogEntityInterface
{
    /**
     * @return string[]
     */
    public function getActivityLoggableProperties(): array;

    public function getActivitySubjectId(): string;

    public function getActivitySubjectType(): string;
}
