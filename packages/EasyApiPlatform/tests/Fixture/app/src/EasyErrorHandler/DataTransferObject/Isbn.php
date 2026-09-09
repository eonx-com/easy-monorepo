<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\DataTransferObject;

final readonly class Isbn
{
    public function __construct(
        public string $value,
    ) {}
}
