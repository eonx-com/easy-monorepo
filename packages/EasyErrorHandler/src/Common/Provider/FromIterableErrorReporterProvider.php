<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Provider;

final class FromIterableErrorReporterProvider implements ErrorReporterProviderInterface
{
    /**
     * @param iterable<\EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface> $reporters
     */
    public function __construct(
        private readonly iterable $reporters,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface>
     */
    public function getReporters(): iterable
    {
        return $this->reporters;
    }
}
