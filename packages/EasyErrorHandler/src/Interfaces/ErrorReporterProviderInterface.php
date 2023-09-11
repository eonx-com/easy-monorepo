<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface ErrorReporterProviderInterface
{
    /**
     * @return iterable<\EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface>
     */
    public function getReporters(): iterable;
}
