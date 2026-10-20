<?php
declare(strict_types=1);

namespace EonX\EasyTest\PhpUnit\Extension;

use EonX\EasyTest\PhpUnit\Subscriber\EasyErrorHandlerTestFailedSubscriber;
use EonX\EasyTest\PhpUnit\Subscriber\EasyErrorHandlerTestFinishedSubscriber;
use EonX\EasyTest\PhpUnit\Subscriber\HttpClientTestFailedSubscriber;
use EonX\EasyTest\PhpUnit\Subscriber\MonologTestFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class EasyTestExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        // When test fails, PHPUnit will not call the `tearDown` method, so we register a subscriber to show errors
        $facade->registerSubscriber(new EasyErrorHandlerTestFailedSubscriber());
        $facade->registerSubscriber(new HttpClientTestFailedSubscriber());

        // Reset stubs here to do this in the same place
        $facade->registerSubscriber(new EasyErrorHandlerTestFinishedSubscriber());
        $facade->registerSubscriber(new MonologTestFinishedSubscriber());
    }
}
