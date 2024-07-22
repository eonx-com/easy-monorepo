<?php
declare(strict_types=1);

namespace EonX\EasyTest\Faker\Generator;

use Faker\Generator;

final class UniqueGroupGenerator
{
    public function __construct(
        protected Generator $generator,
        protected string $groupName,
    ) {
    }

    public function __call(string $name, mixed $arguments): UniqueGroupPropertyValueGenerator
    {
        return new UniqueGroupPropertyValueGenerator($this->generator, $arguments, $name, $this->groupName);
    }
}
