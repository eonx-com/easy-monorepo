<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Provider;

final readonly class FromIterableErrorReporterProvider implements ErrorReporterProviderInterface
{
    /**
     * @param iterable<\EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface> $reporters
     */
    public function __construct(
        private iterable $reporters,
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
