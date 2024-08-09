<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\EntityEvent\Event;

use Throwable;

final readonly class WrapInTransactionExceptionEvent
{
    public function __construct(
        private Throwable $throwable,
    ) {
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
