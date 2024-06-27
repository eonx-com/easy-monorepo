<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject;

final class CategoryInputDtoWithConstructor
{
    public function __construct(
        public string $name,
        public int $rank,
    ) {
    }
}
