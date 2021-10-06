<?php

declare(strict_types=1);

namespace EonX\EasyDoctrine\Events;

final class TransactionalExceptionEvent
{
    /**
     * @var \Throwable
     */
    private $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }
}
