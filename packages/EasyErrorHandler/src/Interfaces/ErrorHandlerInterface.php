<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @method \EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface[] getBuilders()
 * @method \EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface[] getReporters()
 */
interface ErrorHandlerInterface
{
    public function isVerbose(): bool;

    public function render(Request $request, Throwable $throwable): Response;

    public function report(Throwable $throwable): void;
}
