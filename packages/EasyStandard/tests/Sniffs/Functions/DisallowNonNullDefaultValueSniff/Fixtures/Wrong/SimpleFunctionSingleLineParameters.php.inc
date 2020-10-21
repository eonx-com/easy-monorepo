<?php

declare(strict_types=1);

class TestClass {
    public const TEST = 'no-matter';
}

function someFunction(int $param1, ?stdClass $class, string $const3 = TestClass::TEST, array $param4 = []) {
    // No body needed
}
