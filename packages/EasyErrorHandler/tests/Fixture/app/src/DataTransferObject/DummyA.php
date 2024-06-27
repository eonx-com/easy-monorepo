<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Fixture\App\DataTransferObject;

final class DummyA
{
    public function __construct(
        public string $title,
    ) {
    }
}
