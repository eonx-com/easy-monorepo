<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\DataTransferObject;

final class CategoryInputDtoWithConstructor
{
    public function __construct(
        public string $name,
        public int $rank,
    ) {
    }
}
