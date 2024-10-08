<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Stub\Reporter;

use EonX\EasyErrorHandler\Common\Reporter\ErrorReporterInterface;
use Throwable;

final class ErrorReporterStub implements ErrorReporterInterface
{
    /**
     * @var \Throwable[]
     */
    private array $reported = [];

    public function getPriority(): int
    {
        return 0;
    }

    /**
     * @return \Throwable[]
     */
    public function getReportedErrors(): array
    {
        return $this->reported;
    }

    public function report(Throwable $throwable): void
    {
        $this->reported[] = $throwable;
    }
}
