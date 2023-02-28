<?php

declare(strict_types=1);

namespace EonX\EasyTest\Bridge\Symfony\Mailer\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Event\MessageEvents;

final class MailerMessageLoggerListenerStub implements EventSubscriberInterface
{
    private static MessageEvents $events;

    public function __construct()
    {
        self::$events = new MessageEvents();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => ['onMessage', -255],
        ];
    }

    public static function reset(): void
    {
        self::$events = new MessageEvents();
    }

    public function getEvents(): MessageEvents
    {
        return self::$events;
    }

    public function onMessage(MessageEvent $event): void
    {
        self::$events->add($event);
    }
}
