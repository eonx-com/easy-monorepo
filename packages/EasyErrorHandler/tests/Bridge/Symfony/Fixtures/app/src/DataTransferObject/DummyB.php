<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Bridge\Symfony\Fixtures\App\DataTransferObject;

final class DummyB
{
    public function __construct(
        public string $title,
    ) {
    }
}
