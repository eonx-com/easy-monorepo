<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\ErrorHandler;

interface TraceableErrorHandlerInterface extends ErrorHandlerInterface
{
    /**
     * @return \EonX\EasyErrorHandler\Common\Builder\ErrorResponseBuilderInterface[]
     */
    public function getBuilders(): array;

    /**
     * @return \Symfony\Component\HttpFoundation\Response[]
     */
    public function getRenderedErrorResponses(): array;

    /**
     * @return \Throwable[]
     */
    public function getReportedErrors(): array;

    /**
     * @return \EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface[]
     */
    public function getReporters(): array;
}
