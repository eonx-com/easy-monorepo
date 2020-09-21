<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use Throwable;

interface ErrorReporterInterface
{
    public function getPriority(): int;

    /**
     * @return void|bool
     */
    public function report(Throwable $throwable);
}
