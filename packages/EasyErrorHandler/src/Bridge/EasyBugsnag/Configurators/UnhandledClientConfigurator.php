<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\EasyBugsnag\Configurators;

use Bugsnag\Client;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use EonX\EasyErrorHandler\Bridge\EasyBugsnag\UnhandledCallbackBridge;
use EonX\EasyErrorHandler\Exceptions\BaseException;
use EonX\EasyErrorHandler\Interfaces\Exceptions\LogLevelAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\SeverityAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\StatusCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\SubCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Interfaces\Exceptions\ValidationExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class UnhandledClientConfigurator extends AbstractClientConfigurator
{
    /**
     * @var class-string[]
     */
    private readonly array $handledExceptionClasses;

    /**
     * @param class-string[] $handledExceptionClasses
     */
    public function __construct(
        ?array $handledExceptionClasses = null,
        ?int $priority = null,
    ) {
        $this->handledExceptionClasses = \array_merge($handledExceptionClasses ?? [], [
            // Base exception class
            BaseException::class,
            // Interfaces from package
            LogLevelAwareExceptionInterface::class,
            SeverityAwareExceptionInterface::class,
            StatusCodeAwareExceptionInterface::class,
            SubCodeAwareExceptionInterface::class,
            TranslatableExceptionInterface::class,
            ValidationExceptionInterface::class,
            // Symfony HTTP exceptions
            HttpExceptionInterface::class,
        ]);

        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $func = function (Report $report): void {
            $throwable = $report->getOriginalError();

            if ($throwable instanceof Throwable === false) {
                return;
            }

            // Set unhandled by default
            $report->setUnhandled(true);

            // Use configuration
            foreach ($this->handledExceptionClasses as $exceptionClass) {
                if (\is_a($throwable, $exceptionClass)) {
                    $report->setUnhandled(false);

                    break;
                }
            }
        };

        $bugsnag
            ->getPipeline()
            ->pipe(new UnhandledCallbackBridge($func));
    }
}
