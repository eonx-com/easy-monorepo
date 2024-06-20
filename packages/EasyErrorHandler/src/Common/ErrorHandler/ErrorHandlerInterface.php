<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\ErrorHandler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

interface ErrorHandlerInterface
{
    /**
     * @return \EonX\EasyErrorHandler\Common\Builder\ErrorResponseBuilderInterface[]
     */
    public function getBuilders(): array;

    /**
     * @return \EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface[]
     */
    public function getReporters(): array;

    public function isVerbose(): bool;

    public function render(Request $request, Throwable $throwable): Response;

    public function report(Throwable $throwable): void;
}
