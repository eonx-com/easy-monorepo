<?php

declare(strict_types=1);

namespace EonX\EasyTest\Bridge\PhpUnit\Extension;

use EonX\EasyTest\Bridge\Symfony\Mailer\EventListener\MailerMessageLoggerListenerStub;
use PHPUnit\Event\Test\Finished as TestFinishedEvent;
use PHPUnit\Event\Test\FinishedSubscriber as TestFinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class MailerMessageLoggerListenerPhpUnitExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class() implements TestFinishedSubscriber {
            public function notify(TestFinishedEvent $event): void
            {
                // We will do this reset even if MailerMessageLoggerListenerStub is not enabled.
                // This is faster because we don't need to boot the kernel to get the bundle config
                MailerMessageLoggerListenerStub::reset();
            }
        });
    }
}
