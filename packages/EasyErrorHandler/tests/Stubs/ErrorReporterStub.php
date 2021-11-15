<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Stubs;

use EonX\EasyErrorHandler\Interfaces\ErrorReporterInterface;
use Throwable;

final class ErrorReporterStub implements ErrorReporterInterface
{
    /**
     * @var \Throwable[]
     */
    private $reported = [];

    public function getPriority(): int
    {
        return 0;
    }

    public function report(Throwable $throwable): void
    {
        $this->reported[] = $throwable;
    }

    /**
     * @return \Throwable[]
     */
    public function getReportedErrors(): array
    {
        return $this->reported;
    }
}
