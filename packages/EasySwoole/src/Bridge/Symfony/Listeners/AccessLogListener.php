<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Symfony\Listeners;

use EonX\EasySwoole\Interfaces\HttpFoundationAccessLogFormatterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class AccessLogListener extends AbstractTerminateEventListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HttpFoundationAccessLogFormatterInterface $accessLogFormatter
    ) {
    }

    protected function doInvoke(TerminateEvent $event): void
    {
        $this->logger->debug(
            $this->accessLogFormatter->formatAccessLog($event->getRequest(), $event->getResponse())
        );
    }
}
