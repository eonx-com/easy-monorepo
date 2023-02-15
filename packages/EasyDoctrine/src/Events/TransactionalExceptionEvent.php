<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Events;

use Throwable;

final class TransactionalExceptionEvent
{
    /**
     * @var \Throwable
     */
    private $throwable;

    public function __construct(Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
