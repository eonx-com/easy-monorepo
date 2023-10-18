<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\DataTransferObject;

final class CategoryInputDtoWithConstructor
{
    public function __construct(
        public string $name,
        public int $rank,
    ) {
    }
}
