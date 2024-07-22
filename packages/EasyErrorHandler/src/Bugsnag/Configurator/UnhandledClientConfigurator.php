<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bugsnag\Configurator;

use Bugsnag\Client;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurator\AbstractClientConfigurator;
use EonX\EasyErrorHandler\Bugsnag\Helper\UnhandledCallbackBridgeHelper;
use EonX\EasyErrorHandler\Common\Exception\BaseException;
use EonX\EasyErrorHandler\Common\Exception\LogLevelAwareExceptionInterface;
use EonX\EasyErrorHandler\Common\Exception\SeverityAwareExceptionInterface;
use EonX\EasyErrorHandler\Common\Exception\StatusCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Common\Exception\SubCodeAwareExceptionInterface;
use EonX\EasyErrorHandler\Common\Exception\TranslatableExceptionInterface;
use EonX\EasyErrorHandler\Common\Exception\WithErrorListExceptionInterface;
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
            WithErrorListExceptionInterface::class,
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
            ->pipe(new UnhandledCallbackBridgeHelper($func));
    }
}
