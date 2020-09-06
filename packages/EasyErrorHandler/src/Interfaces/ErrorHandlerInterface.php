<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface ErrorHandlerInterface
{
    public function render(Throwable $throwable): Response;

    public function report(Throwable $throwable): void;
}
