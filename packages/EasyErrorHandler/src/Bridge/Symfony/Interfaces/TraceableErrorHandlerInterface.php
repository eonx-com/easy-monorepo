<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Interfaces;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;
use Throwable;

interface TraceableErrorHandlerInterface extends ErrorHandlerInterface
{
    public function getReportedException(): ?Throwable;
}
