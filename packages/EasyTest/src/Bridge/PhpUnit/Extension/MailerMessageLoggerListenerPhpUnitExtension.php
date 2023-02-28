<?php

declare(strict_types=1);

namespace EonX\EasyTest\Bridge\PhpUnit\Extension;

use EonX\EasyTest\Bridge\Symfony\Mailer\EventListener\MailerMessageLoggerListenerStub;
use PHPUnit\Runner\AfterTestHook;

final class MailerMessageLoggerListenerPhpUnitExtension implements AfterTestHook
{
    public function executeAfterTest(string $test, float $time): void
    {
        // We will do this reset even if MailerMessageLoggerListenerStub is not enabled.
        // This is faster because we don't need to boot the kernel to get the bundle config.
        MailerMessageLoggerListenerStub::reset();
    }
}
