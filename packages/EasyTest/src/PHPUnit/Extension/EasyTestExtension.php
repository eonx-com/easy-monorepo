<?php
declare(strict_types=1);

namespace EonX\EasyTest\PHPUnit\Extension;

use EonX\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandlerInterface;
use EonX\EasyTest\PHPUnit\Subscriber\EasyErrorHandlerTestFailedSubscriber;
use EonX\EasyTest\PHPUnit\Subscriber\EasyErrorHandlerTestFinishedSubscriber;
use EonX\EasyTest\PHPUnit\Subscriber\HttpClientTestFailedSubscriber;
use EonX\EasyTest\PHPUnit\Subscriber\MonologTestFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class EasyTestExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        // EasyErrorHandler is optional, its stub cannot be loaded without it
        $isEasyErrorHandlerInstalled = \interface_exists(TraceableErrorHandlerInterface::class);

        // When test fails, PHPUnit will not call the `tearDown` method, so we register a subscriber to show errors
        if ($isEasyErrorHandlerInstalled) {
            $facade->registerSubscriber(new EasyErrorHandlerTestFailedSubscriber());
        }
        $facade->registerSubscriber(new HttpClientTestFailedSubscriber());

        // Reset stubs here to do this in the same place
        if ($isEasyErrorHandlerInstalled) {
            $facade->registerSubscriber(new EasyErrorHandlerTestFinishedSubscriber());
        }
        $facade->registerSubscriber(new MonologTestFinishedSubscriber());
    }
}
