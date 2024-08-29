<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\ErrorHandler;

interface TraceableErrorHandlerInterface extends ErrorHandlerInterface
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response[]
     */
    public function getRenderedErrorResponses(): array;

    /**
     * @return \Throwable[]
     */
    public function getReportedErrors(): array;
}
