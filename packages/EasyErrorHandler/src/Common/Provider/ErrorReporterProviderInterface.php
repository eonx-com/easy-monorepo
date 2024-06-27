<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Provider;

interface ErrorReporterProviderInterface
{
    /**
     * @return iterable<\EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface>
     */
    public function getReporters(): iterable;
}
