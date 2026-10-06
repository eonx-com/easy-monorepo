<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\Laravel\ExceptionHandler;

use Illuminate\Contracts\Debug\ExceptionHandler;
use RuntimeException;
use Throwable;

final class ExceptionHandlerStub implements ExceptionHandler
{
    /**
     * @var \Throwable[]
     */
    private array $reportedThrowables = [];

    public function getReportedThrowables(): array
    {
        return $this->reportedThrowables;
    }

    public function render($request, Throwable $e): never
    {
        throw new RuntimeException('Not supported');
    }

    public function renderForConsole($output, Throwable $e): void
    {
    }

    public function report(Throwable $e): void
    {
        $this->reportedThrowables[] = $e;
    }

    public function shouldReport(Throwable $e): bool
    {
        return true;
    }
}
