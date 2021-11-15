<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Reporters;

use EonX\EasyErrorHandler\Interfaces\ErrorReporterProviderInterface;

final class FromIterableReporterProvider implements ErrorReporterProviderInterface
{
    /**
     * @var iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface>
     */
    private $reporters;

    /**
     * @param iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface> $reporters
     */
    public function __construct(iterable $reporters)
    {
        $this->reporters = $reporters;
    }

    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface>
     */
    public function getReporters(): iterable
    {
        return $this->reporters;
    }
}
