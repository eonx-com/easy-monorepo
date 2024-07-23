<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Fixture\SensitiveData;

final class DummyObject
{
    public function __construct(
        public string $prop1,
        public string $prop2,
        public string $prop3,
    ) {
    }
}
