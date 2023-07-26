<?php

declare(strict_types=1);

namespace EonX\EasyTest\Bridge\Symfony\Mailer\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Event\MessageEvents;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;

final class MailerMessageLoggerListenerStub extends MessageLoggerListener
{
    public function reset(): void
    {
    }
}
