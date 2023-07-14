<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Events;

use Throwable;

final class TransactionalExceptionEvent
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
