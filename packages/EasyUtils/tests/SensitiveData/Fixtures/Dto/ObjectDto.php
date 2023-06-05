<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\SensitiveData\Fixtures\Dto;

final class ObjectDto
{
    public function __construct(
        public string $prop1,
        public string $prop2,
        public string $prop3,
    ) {
    }
}
