<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Providers;

use EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface;

final class FromIterableErrorReporterProvider implements ErrorReporterProviderInterface
{
    /**
     * @param iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface> $reporters
     */
    public function __construct(
        private readonly iterable $reporters,
    ) {
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface>
     */
    public function getReporters(): iterable
    {
        return $this->reporters;
    }
}
